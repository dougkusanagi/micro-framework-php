<?php

namespace App\Controllers;

use GuepardoSys\Core\Request;
use GuepardoSys\Core\Response;

/**
 * ProductController Controller
 */
class ProductController extends BaseController
{
    /**
     * Display a listing of the resource
     */
    public function index(): string
    {
        return view('products.index');
    }

    /**
     * Show the form for creating a new resource
     */
    public function create(): string
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage
     */
    public function store(Request $request): string
    {
        // Validate and store the resource
        $data = $request->all();
        
        // TODO: Implement storage logic
        
        return $this->index();
    }

    /**
     * Display the specified resource
     */
    public function show(Request $request): string
    {
        $id = $request->getRouteParam('id');
        
        // TODO: Implement show logic
        
        return view('products.show', ['id' => $id]);
    }

    /**
     * Show the form for editing the specified resource
     */
    public function edit(Request $request): string
    {
        $id = $request->getRouteParam('id');
        
        // TODO: Implement edit logic
        
        return view('products.edit', ['id' => $id]);
    }

    /**
     * Update the specified resource in storage
     */
    public function update(Request $request): string
    {
        $id = $request->getRouteParam('id');
        $data = $request->all();
        
        // TODO: Implement update logic
        
        return $this->show($request);
    }

    /**
     * Remove the specified resource from storage
     */
    public function destroy(Request $request): string
    {
        $id = $request->getRouteParam('id');
        
        // TODO: Implement destroy logic
        
        return $this->index();
    }
}
