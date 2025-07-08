<?php

namespace GuepardoSys\Core\View;

use Exception;

/**
 * Template Engine inspirado no Blade
 * Compila templates customizados para PHP puro
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
        
        // Criar diretório de cache se não existir
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
        if (preg_match('/@extends\([\'"](.+?)[\'"]\)/', $content, $matches)) {
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
        // Capturar seções
        $content = preg_replace_callback(
            '/@section\([\'"](.+?)[\'"]\)(.*?)@endsection/s',
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
            '/@yield\([\'"](.+?)[\'"]\)/',
            function ($matches) {
                $sectionName = $matches[1];
                return '<?php if (isset($__sections["' . $sectionName . '"])): ?><?= $__sections["' . $sectionName . '"] ?><?php endif; ?>';
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
            '/@include\([\'"](.+?)[\'"]\)/',
            function ($matches) {
                $includePath = str_replace('.', '/', $matches[1]);
                return '<?php include "' . $this->viewsPath . '/' . $includePath . '.php"; ?>';
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
            return '<?php if (' . $matches[1] . '): ?>';
        }, $content);
        
        // @elseif
        $content = preg_replace_callback('/@elseif\s*\((.+?)\)/', function($matches) {
            return '<?php elseif (' . $matches[1] . '): ?>';
        }, $content);
        
        // @else
        $content = str_replace('@else', '<?php else: ?>', $content);
        
        // @endif
        $content = str_replace('@endif', '<?php endif; ?>', $content);
        
        return $content;
    }

    /**
     * Compila loops (@foreach, @endforeach)
     */
    private function compileLoops(string $content): string
    {
        // @foreach - usando str_replace simples por enquanto
        $content = str_replace('@foreach(', '<?php foreach(', $content);
        $content = str_replace('@endforeach', '<?php endforeach; ?>', $content);
        
        // @for - usando str_replace simples por enquanto  
        $content = str_replace('@for(', '<?php for(', $content);
        $content = str_replace('@endfor', '<?php endfor; ?>', $content);
        
        return $content;
    }

    /**
     * Renderiza o arquivo compilado
     */
    private function renderCompiled(string $cacheFile): string
    {
        // Disponibilizar variáveis no escopo
        extract($this->data);
        
        // Disponibilizar seções
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
     * Verifica se precisa recompilar
     */
    private function needsCompilation(string $templatePath, string $cacheFile): bool
    {
        if (!file_exists($cacheFile)) {
            return true;
        }
        
        return filemtime($templatePath) > filemtime($cacheFile);
    }

    /**
     * Limpa o cache
     */
    public function clearCache(): void
    {
        $files = glob($this->cachePath . '/*.php');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
