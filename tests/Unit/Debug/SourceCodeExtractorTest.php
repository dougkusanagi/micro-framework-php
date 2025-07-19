<?php

function createTestFile(): string {
    $testFile = sys_get_temp_dir() . '/test_source_' . uniqid() . '.php';
    $testContent = "<?php\n" .
        "// This is a test file\n" .
        "class TestClass {\n" .
        "    public function testMethod() {\n" .
        "        \$variable = 'test';\n" .
        "        return \$variable;\n" .
        "    }\n" .
        "}\n" .
        "\n" .
        "// End of file\n";
    
    file_put_contents($testFile, $testContent);
    return $testFile;
}

// Extract method tests
test('extract method should extract code lines around specified line', function () {
    $extractor = new \GuepardoSys\Core\Debug\SourceCodeExtractor();
    $testFile = createTestFile();
    
    $result = $extractor->extract($testFile, 5, 2);
    
    expect($result)->toHaveKey('lines');
    expect($result)->toHaveKey('highlighted_line');
    expect($result)->toHaveKey('start_line');
    expect($result)->toHaveKey('end_line');
    expect($result['error'])->toBeNull();
    
    expect($result['highlighted_line'])->toBe(5);
    expect($result['start_line'])->toBe(3);
    expect($result['end_line'])->toBe(7);
    
    // Should have 5 lines (3, 4, 5, 6, 7)
    expect(count($result['lines']))->toBe(5);
    
    // Line 5 should be highlighted
    expect($result['lines'][5]['is_highlighted'])->toBeTrue();
    expect($result['lines'][4]['is_highlighted'])->toBeFalse();
    
    unlink($testFile);
});

test('extract method should handle edge case when line is at beginning of file', function () {
    $extractor = new \GuepardoSys\Core\Debug\SourceCodeExtractor();
    $testFile = createTestFile();
    
    $result = $extractor->extract($testFile, 1, 3);
    
    expect($result['start_line'])->toBe(1);
    expect($result['highlighted_line'])->toBe(1);
    expect($result['lines'][1]['is_highlighted'])->toBeTrue();
    
    unlink($testFile);
});

test('extract method should handle edge case when line is at end of file', function () {
    $extractor = new \GuepardoSys\Core\Debug\SourceCodeExtractor();
    $testFile = createTestFile();
    
    $result = $extractor->extract($testFile, 10, 3);
    
    expect($result['highlighted_line'])->toBe(10);
    expect($result['lines'][10]['is_highlighted'])->toBeTrue();
    
    unlink($testFile);
});

test('extract method should handle non-existent file gracefully', function () {
    $extractor = new \GuepardoSys\Core\Debug\SourceCodeExtractor();
    
    $result = $extractor->extract('/non/existent/file.php', 5, 2);
    
    expect($result['error'])->toContain('File not found or not readable');
    expect($result['lines'])->toBeEmpty();
});

test('extract method should handle invalid line numbers', function () {
    $extractor = new \GuepardoSys\Core\Debug\SourceCodeExtractor();
    $testFile = createTestFile();
    
    $result = $extractor->extract($testFile, -5, 2);
    
    expect($result['highlighted_line'])->toBe(1);
    expect($result['start_line'])->toBe(1);
    
    unlink($testFile);
});

test('extract method should limit context lines to maximum allowed', function () {
    $extractor = new \GuepardoSys\Core\Debug\SourceCodeExtractor();
    $testFile = createTestFile();
    
    $result = $extractor->extract($testFile, 5, 100);
    
    // Should not crash and should limit the context
    expect($result)->toHaveKey('lines');
    expect($result['error'])->toBeNull();
    
    unlink($testFile);
});

test('extract method should handle zero context lines', function () {
    $extractor = new \GuepardoSys\Core\Debug\SourceCodeExtractor();
    $testFile = createTestFile();
    
    $result = $extractor->extract($testFile, 5, 0);
    
    expect($result['start_line'])->toBe(5);
    expect($result['end_line'])->toBe(5);
    expect(count($result['lines']))->toBe(1);
    expect($result['lines'][5]['is_highlighted'])->toBeTrue();
    
    unlink($testFile);
});

test('extract method should preserve line content accurately', function () {
    $extractor = new \GuepardoSys\Core\Debug\SourceCodeExtractor();
    $testFile = createTestFile();
    
    $result = $extractor->extract($testFile, 5, 1);
    
    // Line 5 contains: "        $variable = 'test';"
    expect($result['lines'][5]['content'])->toContain("\$variable = 'test';");
    
    unlink($testFile);
});

// highlightSyntax method tests
test('highlightSyntax method should highlight PHP code with HTML tags', function () {
    $extractor = new \GuepardoSys\Core\Debug\SourceCodeExtractor();
    
    $code = "\$variable = 'test';";
    $highlighted = $extractor->highlightSyntax($code, 'php');
    
    expect($highlighted)->toContain('class="php-');
    expect($highlighted)->toContain('span');
});

test('highlightSyntax method should handle non-PHP code by escaping HTML', function () {
    $extractor = new \GuepardoSys\Core\Debug\SourceCodeExtractor();
    
    $code = '<script>alert("test");</script>';
    $highlighted = $extractor->highlightSyntax($code, 'javascript');
    
    expect($highlighted)->toBe(htmlspecialchars($code, ENT_QUOTES, 'UTF-8'));
    expect($highlighted)->not->toContain('<script>');
});

test('highlightSyntax method should handle empty code', function () {
    $extractor = new \GuepardoSys\Core\Debug\SourceCodeExtractor();
    
    $highlighted = $extractor->highlightSyntax('', 'php');
    expect($highlighted)->toBe('');
});

test('highlightSyntax method should handle code with special characters', function () {
    $extractor = new \GuepardoSys\Core\Debug\SourceCodeExtractor();
    
    $code = "\$var = \"test with <>&'\" quotes\";";
    $highlighted = $extractor->highlightSyntax($code, 'php');
    
    // Should contain highlighting classes
    expect($highlighted)->toContain('class="php-');
    // Should properly escape HTML entities in the content
    expect($highlighted)->toContain('&lt;');
    expect($highlighted)->toContain('&gt;');
});

// getHighlightingCSS method tests
test('getHighlightingCSS method should return CSS styles for syntax highlighting', function () {
    $extractor = new \GuepardoSys\Core\Debug\SourceCodeExtractor();
    
    $css = $extractor->getHighlightingCSS();
    
    expect($css)->toContain('.source-code');
    expect($css)->toContain('.source-line');
    expect($css)->toContain('.line-number');
    expect($css)->toContain('.line-content');
    expect($css)->toContain('.php-default');
    expect($css)->toContain('.php-keyword');
    expect($css)->toContain('.php-string');
    expect($css)->toContain('.php-comment');
});

// Integration scenarios
test('integration should handle a complete extraction with highlighting', function () {
    $extractor = new \GuepardoSys\Core\Debug\SourceCodeExtractor();
    $testFile = createTestFile();
    
    $result = $extractor->extract($testFile, 5, 2);
    
    // Verify structure
    expect($result['error'])->toBeNull();
    expect($result['lines'])->not->toBeEmpty();
    
    // Verify each line has required properties
    foreach ($result['lines'] as $lineNum => $lineData) {
        expect($lineData)->toHaveKey('number');
        expect($lineData)->toHaveKey('content');
        expect($lineData)->toHaveKey('is_highlighted');
        expect($lineData)->toHaveKey('highlighted_content');
        
        expect($lineData['number'])->toBe($lineNum);
        expect(is_string($lineData['content']))->toBeTrue();
        expect(is_bool($lineData['is_highlighted']))->toBeTrue();
        expect(is_string($lineData['highlighted_content']))->toBeTrue();
    }
    
    unlink($testFile);
});

test('integration should handle file with different line endings', function () {
    $extractor = new \GuepardoSys\Core\Debug\SourceCodeExtractor();
    
    // Create file with Windows line endings
    $windowsFile = sys_get_temp_dir() . '/test_windows_' . uniqid() . '.php';
    $windowsContent = "<?php\r\n\$var = 'test';\r\necho \$var;\r\n";
    file_put_contents($windowsFile, $windowsContent);
    
    $result = $extractor->extract($windowsFile, 2, 1);
    
    expect($result['error'])->toBeNull();
    expect($result['lines'][2]['content'])->toContain("\$var = 'test';");
    
    unlink($windowsFile);
});

test('integration should handle very large files efficiently', function () {
    $extractor = new \GuepardoSys\Core\Debug\SourceCodeExtractor();
    
    // Create a larger test file
    $largeFile = sys_get_temp_dir() . '/test_large_' . uniqid() . '.php';
    $content = "<?php\n";
    for ($i = 1; $i <= 1000; $i++) {
        $content .= "// Line $i\n";
    }
    file_put_contents($largeFile, $content);
    
    $result = $extractor->extract($largeFile, 500, 5);
    
    expect($result['error'])->toBeNull();
    expect($result['start_line'])->toBe(495);
    expect($result['end_line'])->toBe(505);
    expect(count($result['lines']))->toBe(11);
    
    unlink($largeFile);
});