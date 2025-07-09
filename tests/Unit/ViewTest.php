<?php

use GuepardoSys\Core\View\View;

describe('View System', function () {
    beforeEach(function () {
        $this->viewsDir = __DIR__ . '/../../temp_views';
        $this->cacheDir = __DIR__ . '/../../temp_cache';

        if (!is_dir($this->viewsDir)) {
            mkdir($this->viewsDir, 0755, true);
        }
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    });

    afterEach(function () {
        // Clean up test files
        $this->cleanupDirectory($this->viewsDir);
        $this->cleanupDirectory($this->cacheDir);
    });

    it('can render simple view', function () {
        $viewContent = 'Hello {{ $name }}!';
        $viewPath = $this->viewsDir . '/simple.guepardo.php';
        file_put_contents($viewPath, $viewContent);

        $view = new View();
        $result = $view->make('simple', ['name' => 'World'], $this->viewsDir);

        expect($result)->toContain('Hello World!');
    });

    it('escapes variables by default', function () {
        $viewContent = 'Content: {{ $content }}';
        $viewPath = $this->viewsDir . '/escape.guepardo.php';
        file_put_contents($viewPath, $viewContent);

        $view = new View();
        $result = $view->make('escape', ['content' => '<script>alert("XSS")</script>'], $this->viewsDir);

        expect($result)->toContain('&lt;script&gt;');
        expect($result)->not->toContain('<script>alert("XSS")</script>');
    });

    it('can handle conditional statements', function () {
        $viewContent = '@if($show)Visible content@endif';
        $viewPath = $this->viewsDir . '/conditional.guepardo.php';
        file_put_contents($viewPath, $viewContent);

        $view = new View();

        // Test with true condition
        $result1 = $view->make('conditional', ['show' => true], $this->viewsDir);
        expect($result1)->toContain('Visible content');

        // Test with false condition
        $result2 = $view->make('conditional', ['show' => false], $this->viewsDir);
        expect($result2)->not->toContain('Visible content');
    });

    it('can handle extends and yields', function () {
        // Create layout
        $layoutContent = '<!DOCTYPE html><html><head><title>@yield("title")</title></head><body>@yield("content")</body></html>';
        file_put_contents($this->viewsDir . '/layout.guepardo.php', $layoutContent);

        // Create child view
        $childContent = '@extends("layout")@section("title")Test Page@endsection@section("content")<h1>Welcome</h1>@endsection';
        file_put_contents($this->viewsDir . '/child.guepardo.php', $childContent);

        $view = new View();
        $result = $view->make('child', [], $this->viewsDir);

        expect($result)->toContain('<title>Test Page</title>');
        expect($result)->toContain('<h1>Welcome</h1>');
    });

    it('can handle includes', function () {
        // Create partial
        $partialContent = '<div class="header">{{ $siteName }}</div>';
        file_put_contents($this->viewsDir . '/header.guepardo.php', $partialContent);

        // Create main view
        $mainContent = '@include("header")<main>Content here</main>';
        file_put_contents($this->viewsDir . '/main.guepardo.php', $mainContent);

        $view = new View();
        $result = $view->make('main', ['siteName' => 'My Site'], $this->viewsDir);

        expect($result)->toContain('<div class="header">My Site</div>');
        expect($result)->toContain('<main>Content here</main>');
    });

    it('can handle foreach loops', function () {
        $viewContent = '<ul>@foreach($items as $item)<li>{{ $item }}</li>@endforeach</ul>';
        file_put_contents($this->viewsDir . '/loop.guepardo.php', $viewContent);

        $view = new View();
        $result = $view->make('loop', ['items' => ['Apple', 'Banana', 'Cherry']], $this->viewsDir);

        expect($result)->toContain('<ul>');
        expect($result)->toContain('<li>Apple</li>');
        expect($result)->toContain('<li>Banana</li>');
        expect($result)->toContain('<li>Cherry</li>');
        expect($result)->toContain('</ul>');
    });

    it('can output unescaped content', function () {
        $viewContent = '<div>{!! $htmlContent !!}</div>';
        file_put_contents($this->viewsDir . '/unescaped.guepardo.php', $viewContent);

        $view = new View();
        $result = $view->make('unescaped', ['htmlContent' => '<strong>Bold Text</strong>'], $this->viewsDir);

        expect($result)->toContain('<strong>Bold Text</strong>');
        expect($result)->not->toContain('&lt;strong&gt;');
    });

    it('can handle if-else statements', function () {
        $viewContent = '@if($user)<p>Hello {{ $user }}</p>@else<p>Please login</p>@endif';
        file_put_contents($this->viewsDir . '/ifelse.guepardo.php', $viewContent);

        $view = new View();

        // Test with user
        $result1 = $view->make('ifelse', ['user' => 'John'], $this->viewsDir);
        expect($result1)->toContain('<p>Hello John</p>');

        // Test without user
        $result2 = $view->make('ifelse', ['user' => null], $this->viewsDir);
        expect($result2)->toContain('<p>Please login</p>');
    });

    it('caches compiled views', function () {
        $viewContent = '<h1>{{ $title }}</h1>';
        file_put_contents($this->viewsDir . '/cached.guepardo.php', $viewContent);

        $view = new View();
        $view->setCachePath($this->cacheDir);
        $view->make('cached', ['title' => 'Test'], $this->viewsDir);

        // Check if cache file was created
        $cacheFiles = glob($this->cacheDir . '/*');
        expect(count($cacheFiles))->toBeGreaterThan(0);
    });

    it('can handle nested view directories', function () {
        $subDir = $this->viewsDir . '/admin';
        mkdir($subDir, 0755, true);

        $viewContent = '<h1>Admin Panel: {{ $title }}</h1>';
        file_put_contents($subDir . '/dashboard.guepardo.php', $viewContent);

        $view = new View();
        $result = $view->make('admin.dashboard', ['title' => 'Dashboard'], $this->viewsDir);

        expect($result)->toContain('<h1>Admin Panel: Dashboard</h1>');
    });

    it('throws exception for non-existent view', function () {
        $view = new View();

        expect(function () use ($view) {
            $view->make('non_existent', [], $this->viewsDir);
        })->toThrow(Exception::class);
    });
});

function cleanupDirectory($dir)
{
    if (is_dir($dir)) {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                cleanupDirectory($path);
                rmdir($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
