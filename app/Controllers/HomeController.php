<?php

namespace App\Controllers;

/**
 * Home Controller
 * 
 * Handles requests for the home page and basic routes
 */
class HomeController extends BaseController
{
    /**
     * Display the home page
     */
    public function index(): string
    {
        $data = [
            'title' => 'Welcome to GuepardoSys',
            'message' => 'Your lightweight PHP framework is working!',
            'version' => '1.0.0',
            'features' => [
                'Lightweight MVC Framework',
                'Simple Routing System',
                'Dependency Injection Container',
                'Minimal File Structure',
                'Perfect for Shared Hosting'
            ]
        ];

        return $this->view('pages.home', $data);
    }

    /**
     * Display the about page
     */
    public function about(): string
    {
        $data = [
            'title' => 'About GuepardoSys',
            'description' => 'GuepardoSys is a micro PHP framework designed for shared hosting environments.',
        ];

        return $this->view('pages.about', $data);
    }
}
