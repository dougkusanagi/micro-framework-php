<?php

namespace GuepardoSys\Core\Debug;

/**
 * Extracts and formats source code around error locations
 * with syntax highlighting capabilities
 */
class SourceCodeExtractor
{
    /**
     * Default number of context lines to show around the error
     */
    private const DEFAULT_CONTEXT_LINES = 10;

    /**
     * Maximum number of context lines allowed
     */
    private const MAX_CONTEXT_LINES = 50;

    /**
     * Extract source code lines around a specific line in a file
     *
     * @param string $file The file path to extract from
     * @param int $line The line number where the error occurred
     * @param int $contextLines Number of lines to show before and after the error line (null to use config)
     * @return array Array containing extracted code with line numbers and highlighting info
     */
    public function extract(string $file, int $line, ?int $contextLines = null): array
    {
        // Check if source code display is enabled
        if (!\GuepardoSys\Core\Debug\DebugConfig::showSource()) {
            return [
                'error' => 'Source code display is disabled',
                'lines' => [],
                'highlighted_line' => $line,
                'start_line' => 1,
                'end_line' => 1
            ];
        }

        // Validate inputs
        if (!is_file($file) || !is_readable($file)) {
            return [
                'error' => 'File not found or not readable: ' . $file,
                'lines' => [],
                'highlighted_line' => $line,
                'start_line' => 1,
                'end_line' => 1
            ];
        }

        if ($line < 1) {
            $line = 1;
        }

        // Use configuration value if not provided
        if ($contextLines === null) {
            $contextLines = \GuepardoSys\Core\Debug\DebugConfig::getContextLines();
        }

        // Limit context lines to prevent memory issues
        $contextLines = min($contextLines, self::MAX_CONTEXT_LINES);
        $contextLines = max($contextLines, 0);

        // Calculate start and end lines
        $startLine = max(1, $line - $contextLines);
        $endLine = $line + $contextLines;

        // Get file lines
        $fileLines = $this->getFileLines($file, $startLine, $endLine);
        
        if (empty($fileLines)) {
            return [
                'error' => 'Unable to read file contents',
                'lines' => [],
                'highlighted_line' => $line,
                'start_line' => $startLine,
                'end_line' => $endLine
            ];
        }

        // Prepare the result
        $result = [
            'error' => null,
            'lines' => [],
            'highlighted_line' => $line,
            'start_line' => $startLine,
            'end_line' => min($endLine, $startLine + count($fileLines) - 1)
        ];

        // Process each line
        $currentLine = $startLine;
        foreach ($fileLines as $content) {
            $result['lines'][$currentLine] = [
                'number' => $currentLine,
                'content' => $content,
                'is_highlighted' => $currentLine === $line,
                'highlighted_content' => $this->highlightSyntax($content, 'php')
            ];
            $currentLine++;
        }

        return $result;
    }

    /**
     * Apply syntax highlighting to code using PHP's built-in tokenizer with XSS protection
     *
     * @param string $code The code to highlight
     * @param string $language The programming language (currently only 'php' supported)
     * @return string The highlighted code with HTML tags
     */
    public function highlightSyntax(string $code, string $language = 'php'): string
    {
        if ($language !== 'php') {
            // For non-PHP code, return escaped HTML
            return $this->sanitizeOutput(htmlspecialchars($code, ENT_QUOTES, 'UTF-8'));
        }

        // Handle empty code
        if (trim($code) === '') {
            return '';
        }

        // Security: Limit code length to prevent DoS
        if (strlen($code) > 50000) { // 50KB limit per line
            $code = substr($code, 0, 50000) . ' ... [truncated]';
        }

        try {
            // Use PHP's built-in syntax highlighting
            $highlighted = highlight_string('<?php ' . $code, true);
            
            // Remove the opening <?php tag that we added
            $highlighted = preg_replace('/^<code><span style="color: #000000">\s*<span style="color: #0000BB">&lt;\?php&nbsp;<\/span>/', '<code><span style="color: #000000">', $highlighted);
            
            // Clean up the output and add our own CSS classes
            $highlighted = $this->convertHighlightingToClasses($highlighted);
            
            // Security: Sanitize output to prevent XSS
            return $this->sanitizeOutput($highlighted);
            
        } catch (\Exception $e) {
            // Fallback to escaped HTML if highlighting fails
            return $this->sanitizeOutput(htmlspecialchars($code, ENT_QUOTES, 'UTF-8'));
        }
    }

    /**
     * Sanitize output to prevent XSS attacks while preserving syntax highlighting
     *
     * @param string $output The output to sanitize
     * @return string Sanitized output
     */
    private function sanitizeOutput(string $output): string
    {
        // Allow only safe HTML tags and attributes for syntax highlighting
        $allowedTags = [
            'span' => ['class'],
            'code' => ['class'],
            'pre' => ['class']
        ];

        // Remove any script tags or javascript
        $output = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $output);
        $output = preg_replace('/javascript:/i', '', $output);
        $output = preg_replace('/on\w+\s*=/i', '', $output);

        // Remove any style attributes except our safe ones
        $output = preg_replace('/style\s*=\s*["\'][^"\']*["\']/i', '', $output);

        // Ensure only allowed attributes remain
        $output = preg_replace_callback('/<(\w+)([^>]*)>/i', function($matches) use ($allowedTags) {
            $tag = strtolower($matches[1]);
            $attributes = $matches[2];
            
            if (!isset($allowedTags[$tag])) {
                return '';
            }
            
            // Filter attributes
            $allowedAttrs = $allowedTags[$tag];
            $cleanAttributes = '';
            
            if (preg_match_all('/(\w+)\s*=\s*["\']([^"\']*)["\']/', $attributes, $attrMatches, PREG_SET_ORDER)) {
                foreach ($attrMatches as $attrMatch) {
                    $attrName = strtolower($attrMatch[1]);
                    $attrValue = $attrMatch[2];
                    
                    if (in_array($attrName, $allowedAttrs)) {
                        // Additional validation for class attributes
                        if ($attrName === 'class') {
                            $attrValue = preg_replace('/[^a-zA-Z0-9\-_\s]/', '', $attrValue);
                        }
                        $cleanAttributes .= ' ' . $attrName . '="' . htmlspecialchars($attrValue, ENT_QUOTES, 'UTF-8') . '"';
                    }
                }
            }
            
            return '<' . $tag . $cleanAttributes . '>';
        }, $output);

        return $output;
    }

    /**
     * Get specific lines from a file with security validation and performance optimization
     *
     * @param string $file The file path
     * @param int $start The starting line number (1-based)
     * @param int $end The ending line number (1-based)
     * @return array Array of file lines
     */
    private function getFileLines(string $file, int $start, int $end): array
    {
        try {
            // Security: Validate file path to prevent directory traversal
            if (!$this->isValidFilePath($file)) {
                return [];
            }

            // Performance: Check file size before reading
            $fileSize = filesize($file);
            if ($fileSize === false || $fileSize > 10 * 1024 * 1024) { // 10MB limit
                return ['[File too large to display]'];
            }

            // Performance: For small files, read all at once
            if ($fileSize < 100 * 1024) { // 100KB
                return $this->getFileLinesSmall($file, $start, $end);
            }

            // Performance: For large files, use lazy loading approach
            return $this->getFileLinesLarge($file, $start, $end);
            
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Read lines from small files efficiently
     *
     * @param string $file The file path
     * @param int $start The starting line number
     * @param int $end The ending line number
     * @return array Array of file lines
     */
    private function getFileLinesSmall(string $file, int $start, int $end): array
    {
        $content = file_get_contents($file);
        if ($content === false) {
            return [];
        }

        $allLines = explode("\n", $content);
        $totalLines = count($allLines);
        
        $start = max(1, $start);
        $end = min($totalLines, $end);
        
        $lines = [];
        for ($i = $start - 1; $i < $end && $i < $totalLines; $i++) {
            $lines[] = rtrim($allLines[$i], "\r");
        }
        
        return $lines;
    }

    /**
     * Read lines from large files with lazy loading
     *
     * @param string $file The file path
     * @param int $start The starting line number
     * @param int $end The ending line number
     * @return array Array of file lines
     */
    private function getFileLinesLarge(string $file, int $start, int $end): array
    {
        $lines = [];
        $handle = fopen($file, 'r');
        
        if (!$handle) {
            return [];
        }

        $currentLine = 1;
        
        // Skip lines before start efficiently
        while ($currentLine < $start && !feof($handle)) {
            fgets($handle);
            $currentLine++;
        }
        
        // Read only the required lines
        $linesRead = 0;
        $maxLines = $end - $start + 1;
        
        while ($currentLine <= $end && !feof($handle) && $linesRead < $maxLines) {
            $line = fgets($handle);
            if ($line !== false) {
                $lines[] = rtrim($line, "\r\n");
                $linesRead++;
            }
            $currentLine++;
        }
        
        fclose($handle);
        return $lines;
    }

    /**
     * Validate file path to prevent directory traversal attacks
     *
     * @param string $file The file path to validate
     * @return bool True if path is valid and safe
     */
    private function isValidFilePath(string $file): bool
    {
        // Security: Prevent directory traversal
        if (strpos($file, '..') !== false) {
            return false;
        }

        // Security: Ensure file is within allowed directories
        $realPath = realpath($file);
        if ($realPath === false) {
            return false;
        }

        // Security: Check if file is within project root
        $projectRoot = realpath(dirname(dirname(dirname(__DIR__))));
        if ($projectRoot === false || strpos($realPath, $projectRoot) !== 0) {
            return false;
        }

        // Security: Check file permissions
        if (!is_readable($file)) {
            return false;
        }

        return true;
    }

    /**
     * Convert PHP's built-in highlighting colors to CSS classes
     *
     * @param string $highlighted The highlighted HTML from highlight_string()
     * @return string The HTML with CSS classes instead of inline styles
     */
    private function convertHighlightingToClasses(string $highlighted): string
    {
        // Map of PHP highlighting colors to CSS classes
        $colorMap = [
            '#000000' => 'php-default',    // Default text
            '#0000BB' => 'php-keyword',    // PHP keywords, variables
            '#007700' => 'php-string',     // Strings
            '#FF8000' => 'php-comment',    // Comments
            '#DD0000' => 'php-html',       // HTML content
        ];

        // Replace inline styles with CSS classes
        foreach ($colorMap as $color => $class) {
            $highlighted = str_replace(
                'style="color: ' . $color . '"',
                'class="' . $class . '"',
                $highlighted
            );
        }

        // Remove the outer <code> tags since we'll add our own wrapper
        $highlighted = preg_replace('/^<code[^>]*>/', '', $highlighted);
        $highlighted = preg_replace('/<\/code>$/', '', $highlighted);

        return $highlighted;
    }

    /**
     * Get CSS styles for syntax highlighting
     *
     * @return string CSS styles for the highlighting classes
     */
    public function getHighlightingCSS(): string
    {
        return '
        .source-code {
            font-family: "Monaco", "Menlo", "Ubuntu Mono", monospace;
            font-size: 14px;
            line-height: 1.5;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            overflow-x: auto;
        }
        
        .source-line {
            display: flex;
            min-height: 21px;
        }
        
        .source-line.highlighted {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        
        .line-number {
            background: #f1f3f4;
            color: #666;
            padding: 0 8px;
            text-align: right;
            min-width: 40px;
            user-select: none;
            border-right: 1px solid #e9ecef;
        }
        
        .line-content {
            padding: 0 12px;
            flex: 1;
            white-space: pre;
        }
        
        .php-default { color: #000000; }
        .php-keyword { color: #0000BB; font-weight: bold; }
        .php-string { color: #007700; }
        .php-comment { color: #FF8000; font-style: italic; }
        .php-html { color: #DD0000; }
        ';
    }
}