<?php

/**
 * Asset helpers for CDN integration and asset management
 */

if (!function_exists('asset')) {
    /**
     * Generate asset URL
     */
    function asset($path)
    {
        $baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        return $baseUrl . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('css')) {
    /**
     * Include CSS file
     */
    function css($path)
    {
        return '<link rel="stylesheet" href="' . asset('css/' . $path) . '">';
    }
}

if (!function_exists('js')) {
    /**
     * Include JavaScript file
     */
    function js($path)
    {
        return '<script src="' . asset('js/' . $path) . '"></script>';
    }
}

if (!function_exists('cdn_css')) {
    /**
     * Include CSS from CDN
     */
    function cdn_css($url, $integrity = null)
    {
        $tag = '<link rel="stylesheet" href="' . $url . '"';
        if ($integrity) {
            $tag .= ' integrity="' . $integrity . '" crossorigin="anonymous"';
        }
        $tag .= '>';
        return $tag;
    }
}

if (!function_exists('cdn_js')) {
    /**
     * Include JavaScript from CDN
     */
    function cdn_js($url, $integrity = null)
    {
        $tag = '<script src="' . $url . '"';
        if ($integrity) {
            $tag .= ' integrity="' . $integrity . '" crossorigin="anonymous"';
        }
        $tag .= '></script>';
        return $tag;
    }
}

if (!function_exists('google_fonts')) {
    /**
     * Include Google Fonts
     */
    function google_fonts($fonts)
    {
        $fontQuery = str_replace(' ', '+', $fonts);
        return '<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=' . $fontQuery . '&display=swap" rel="stylesheet">';
    }
}

if (!function_exists('alpine_js')) {
    /**
     * Include Alpine.js from CDN
     */
    function alpine_js()
    {
        return '<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>';
    }
}

if (!function_exists('lucide_icons')) {
    /**
     * Include Lucide Icons from CDN
     */
    function lucide_icons()
    {
        return '<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>';
    }
}

if (!function_exists('glide_js')) {
    /**
     * Include Glide.js from CDN
     */
    function glide_js()
    {
        return cdn_css('https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/css/glide.core.min.css') .
            cdn_css('https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/css/glide.theme.min.css') .
            cdn_js('https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/glide.min.js');
    }
}

if (!function_exists('vite_assets')) {
    /**
     * Include Vite assets (for future implementation)
     */
    function vite_assets()
    {
        // Future implementation for hot reload in development
        return '';
    }
}

if (!function_exists('asset_with_version')) {
    /**
     * Generate asset URL with version from manifest
     */
    function asset_with_version($path)
    {
        static $manifest = null;

        if ($manifest === null) {
            $manifestPath = $_SERVER['DOCUMENT_ROOT'] . '/assets/manifest.json';
            $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        }

        $versionedPath = $manifest[$path] ?? $path;
        return asset($versionedPath);
    }
}
