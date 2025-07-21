<?php

namespace GuepardoSys\Core\View;

use Exception;

/**
 * Template Engine inspirado no Blade
 * Compila templates customizados para PHP puro com cache otimizado
 */
class View
{
    private string $viewsPath;
    private string $cachePath;
    private array $data = [];
    private array $sections = [];
    private ?string $extends = null;

    public function __construct(string $viewsPath = null, string $cachePath = null)
    {
        $this->viewsPath = $viewsPath ?? APP_PATH . '/Views';
        $this->cachePath = $cachePath ?? STORAGE_PATH . '/cache/views';
        
        // Criar diretorio de cache se nao existir
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Renderiza uma view
     */
    public function render(string $view, array $data = []): string
    {
        $this->data = array_merge($this->data, $data);
        
        // Converter dots para path (ex: pages.home -> pages/home)
        $viewPath = str_replace('.', '/', $view);
        $templatePath = $this->viewsPath . '/' . $viewPath . '.php';
        
        if (!file_exists($templatePath)) {
            throw new Exception("View not found: {$view}");
        }

        // Verificar cache
        $cacheFile = $this->getCacheFile($templatePath);
        
        if ($this->needsCompilation($templatePath, $cacheFile)) {
            $compiled = $this->compile($templatePath);
            
            // Garantir que o diretório de cache existe
            $cacheDir = dirname($cacheFile);
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            
            // Salvar o arquivo compilado
            file_put_contents($cacheFile, $compiled);
        }

        return $this->renderCompiled($cacheFile);
    }

    /**
     * Compila o template
     */
    private function compile(string $templatePath): string
    {
        $content = file_get_contents($templatePath);
        
        // Reset state
        $this->sections = [];
        $this->extends = null;
        
        // Compilar diretivas
        $content = $this->compileExtends($content);
        $content = $this->compileSections($content);
        $content = $this->compileYields($content);
        $content = $this->compileIncludes($content);
        $content = $this->compileEchos($content);
        $content = $this->compileConditionals($content);
        $content = $this->compileLoops($content);
        
        return $content;
    }

    /**
     * Compila {{ $variavel }} para PHP com escape
     */
    private function compileEchos(string $content): string
    {
        // {!! $variavel !!} -> <?= $variavel ?> (sem escape - deve vir primeiro)
        $content = preg_replace_callback('/\{!!\s*(.+?)\s*!!\}/', function($matches) {
            return '<?= ' . $matches[1] . ' ?>';
        }, $content);
        
        // {{ $variavel }} -> <?= htmlspecialchars($variavel, ENT_QUOTES, 'UTF-8') ?>
        $content = preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/', function($matches) {
            return '<?= htmlspecialchars(' . $matches[1] . ', ENT_QUOTES, "UTF-8") ?>';
        }, $content);
        
        return $content;
    }

    /**
     * Compila @extends
     */
    private function compileExtends(string $content): string
    {
        if (preg_match('/@extends\s*\([\'"](.+?)[\'"]\)/', $content, $matches)) {
            $this->extends = $matches[1];
            $content = str_replace($matches[0], '', $content);
        }
        
        return $content;
    }

    /**
     * Compila @section e @endsection
     */
    private function compileSections(string $content): string
    {
        // Capturar secoes
        $content = preg_replace_callback(
            '/@section\s*\([\'"](.+?)[\'"]\)(.*?)@endsection/s',
            function ($matches) {
                $sectionName = $matches[1];
                $sectionContent = trim($matches[2]);
                $this->sections[$sectionName] = $sectionContent;
                return '';
            },
            $content
        );
        
        return $content;
    }

    /**
     * Compila @yield
     */
    private function compileYields(string $content): string
    {
        $content = preg_replace_callback(
            '/@yield\s*\([\'"](.+?)[\'"]\)/',
            function ($matches) {
                $sectionName = $matches[1];
                return '<?= if (isset($__sections["' . $sectionName . '"])): ?><?= $__sections["' . $sectionName . '"] ?><?php endif; ?>';
            },
            $content
        );
        
        return $content;
    }

    /**
     * Compila @include
     */
    private function compileIncludes(string $content): string
    {
        $content = preg_replace_callback(
            '/@include\s*\([\'"](.+?)[\'"]\)/',
            function ($matches) {
                $includePath = str_replace('.', '/', $matches[1]);
                return '<?= include "' . $this->viewsPath . '/' . $includePath . '.php"; ?>';
            },
            $content
        );
        
        return $content;
    }

    /**
     * Compila condicionais (@if, @else, @endif)
     */
    private function compileConditionals(string $content): string
    {
        // @if
        $content = preg_replace_callback('/@if\s*\((.+?)\)/', function($matches) {
            return '<?= if (' . $matches[1] . '): ?>';
        }, $content);
        
        // @elseif
        $content = preg_replace_callback('/@elseif\s*\((.+?)\)/', function($matches) {
            return '<?= elseif (' . $matches[1] . '): ?>';
        }, $content);
        
        // @else
        $content = str_replace('@else', '<?= else: ?>', $content);
        
        // @endif
        $content = str_replace('@endif', '<?= endif; ?>', $content);
        
        return $content;
    }

    /**
     * Compila loops (@foreach, @endforeach)
     */
    private function compileLoops(string $content): string
    {
        // @foreach
        $content = preg_replace_callback('/@foreach\s*\((.+?)\)/', function($matches) {
            return '<?= foreach(' . $matches[1] . '): ?>';
        }, $content);
        
        // @endforeach
        $content = str_replace('@endforeach', '<?= endforeach; ?>', $content);
        
        // @for
        $content = preg_replace_callback('/@for\s*\((.+?)\)/', function($matches) {
            return '<?= for(' . $matches[1] . '): ?>';
        }, $content);
        
        // @endfor
        $content = str_replace('@endfor', '<?= endfor; ?>', $content);
        
        // @while
        $content = preg_replace_callback('/@while\s*\((.+?)\)/', function($matches) {
            return '<?= while(' . $matches[1] . '): ?>';
        }, $content);
        
        // @endwhile
        $content = str_replace('@endwhile', '<?= endwhile; ?>', $content);
        
        return $content;
    }

    /**
     * Renderiza o arquivo compilado
     */
    private function renderCompiled(string $cacheFile): string
    {
        // Disponibilizar variaveis no escopo
        extract($this->data);
        
        // Disponibilizar secoes
        $__sections = $this->sections;
        
        ob_start();
        
        try {
            include $cacheFile;
            $content = ob_get_clean();
            
            // Se tem extends, processar o layout pai
            if ($this->extends) {
                $layoutView = new self($this->viewsPath, $this->cachePath);
                $layoutView->sections = $this->sections;
                return $layoutView->render($this->extends, $this->data);
            }
            
            return $content;
            
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * Gera nome do arquivo de cache
     */
    private function getCacheFile(string $templatePath): string
    {
        $hash = md5($templatePath);
        return $this->cachePath . '/' . $hash . '.php';
    }

    /**
     * Verifica se precisa recompilar com invalidação inteligente
     */
    private function needsCompilation(string $templatePath, string $cacheFile): bool
    {
        if (!file_exists($cacheFile)) {
            return true;
        }
        
        // Check if template file is newer
        if (filemtime($templatePath) > filemtime($cacheFile)) {
            return true;
        }
        
        // Check if any included templates are newer
        return $this->hasNewerIncludes($templatePath, filemtime($cacheFile));
    }

    /**
     * Check if any included templates are newer than cache
     */
    private function hasNewerIncludes(string $templatePath, int $cacheTime): bool
    {
        $content = file_get_contents($templatePath);
        
        // Find all @include directives
        if (preg_match_all('/@include\s*\([\'"](.+?)[\'"]\)/', $content, $matches)) {
            foreach ($matches[1] as $includePath) {
                $includeFile = $this->viewsPath . '/' . str_replace('.', '/', $includePath) . '.php';
                
                if (file_exists($includeFile) && filemtime($includeFile) > $cacheTime) {
                    return true;
                }
                
                // Recursively check includes in included files
                if (file_exists($includeFile) && $this->hasNewerIncludes($includeFile, $cacheTime)) {
                    return true;
                }
            }
        }
        
        // Check @extends
        if (preg_match('/@extends\s*\([\'"](.+?)[\'"]\)/', $content, $matches)) {
            $layoutFile = $this->viewsPath . '/' . str_replace('.', '/', $matches[1]) . '.php';
            
            if (file_exists($layoutFile) && filemtime($layoutFile) > $cacheTime) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Limpa o cache com opção de limpeza seletiva
     */
    public function clearCache(string $pattern = '*'): int
    {
        $files = glob($this->cachePath . '/' . $pattern);
        $cleared = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $cleared++;
            }
        }
        
        return $cleared;
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        $files = glob($this->cachePath . '/*');
        $totalSize = 0;
        $totalFiles = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $totalFiles++;
                $totalSize += filesize($file);
            }
        }
        
        return [
            'total_files' => $totalFiles,
            'total_size' => $totalSize,
            'cache_path' => $this->cachePath
        ];
    }
}