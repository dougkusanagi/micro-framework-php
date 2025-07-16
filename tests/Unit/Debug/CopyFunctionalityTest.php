<?php

use PHPUnit\Framework\TestCase;

class CopyFunctionalityTest extends TestCase
{
    private $templatePath;
    
    protected function setUp(): void
    {
        $this->templatePath = __DIR__ . '/../../../src/Core/Debug/Templates/error-page.html.php';
    }
    
    public function testTemplateContainsSearchContainer()
    {
        $content = file_get_contents($this->templatePath);
        
        // Check for search container
        $this->assertStringContainsString('search-container', $content);
        $this->assertStringContainsString('search-input', $content);
        $this->assertStringContainsString('search-results', $content);
        $this->assertStringContainsString('search-nav', $content);
    }
    
    public function testTemplateContainsCopyButtons()
    {
        $content = file_get_contents($this->templatePath);
        
        // Check for copy buttons
        $this->assertStringContainsString('📋 Copy', $content);
        $this->assertStringContainsString('copyCode', $content);
        $this->assertStringContainsString('copyFullError', $content);
        $this->assertStringContainsString('copyStackTrace', $content);
    }
    
    public function testTemplateContainsEnhancedAnimations()
    {
        $content = file_get_contents($this->templatePath);
        
        // Check for animation CSS
        $this->assertStringContainsString('transition:', $content);
        $this->assertStringContainsString('max-height', $content);
        $this->assertStringContainsString('transform:', $content);
    }
    
    public function testTemplateContainsSearchFunctionality()
    {
        $content = file_get_contents($this->templatePath);
        
        // Check for search JavaScript functions
        $this->assertStringContainsString('performSearch', $content);
        $this->assertStringContainsString('navigateSearch', $content);
        $this->assertStringContainsString('clearSearch', $content);
        $this->assertStringContainsString('highlightSearchMatches', $content);
    }
    
    public function testTemplateContainsVisualFeedback()
    {
        $content = file_get_contents($this->templatePath);
        
        // Check for visual feedback elements
        $this->assertStringContainsString('copy-feedback', $content);
        $this->assertStringContainsString('showCopyFeedback', $content);
        $this->assertStringContainsString('search-highlight', $content);
        $this->assertStringContainsString('search-match', $content);
    }
    
    public function testTemplateContainsKeyboardShortcuts()
    {
        $content = file_get_contents($this->templatePath);
        
        // Check for keyboard shortcuts
        $this->assertStringContainsString('keydown', $content);
        $this->assertStringContainsString('ctrlKey', $content);
        $this->assertStringContainsString('metaKey', $content);
        $this->assertStringContainsString('Escape', $content);
    }
    
    public function testTemplateContainsCollapsibleAnimations()
    {
        $content = file_get_contents($this->templatePath);
        
        // Check for collapsible section animations
        $this->assertStringContainsString('toggleSection', $content);
        $this->assertStringContainsString('toggleStackFrame', $content);
        $this->assertStringContainsString('collapsed', $content);
        $this->assertStringContainsString('expanded', $content);
    }
    
    public function testTemplateRendersWithSampleData()
    {
        // Create sample error data
        $data = [
            'error' => [
                'type' => 'Test Error',
                'message' => 'This is a test error message',
                'file' => '/path/to/test/file.php',
                'line' => 42
            ],
            'source' => [
                'file' => '/path/to/test/file.php',
                'lines' => [
                    40 => ['highlighted_content' => 'echo "line 40";', 'is_highlighted' => false],
                    41 => ['highlighted_content' => 'echo "line 41";', 'is_highlighted' => false],
                    42 => ['highlighted_content' => 'throw new Exception("test");', 'is_highlighted' => true],
                    43 => ['highlighted_content' => 'echo "line 43";', 'is_highlighted' => false],
                ]
            ],
            'stack_trace' => [
                [
                    'function' => 'testFunction',
                    'file' => '/path/to/test/file.php',
                    'line' => 42,
                    'is_vendor' => false
                ]
            ],
            'context' => [
                'request' => [
                    'method' => 'GET',
                    'url' => '/test'
                ]
            ],
            'suggestions' => [
                'Check your code syntax',
                'Verify file permissions'
            ]
        ];
        
        // Capture output
        ob_start();
        include $this->templatePath;
        $output = ob_get_clean();
        
        // Check that template renders without errors
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('Test Error', $output);
        $this->assertStringContainsString('This is a test error message', $output);
        $this->assertStringContainsString('search-input', $output);
        $this->assertStringContainsString('📋 Copy', $output);
    }
}