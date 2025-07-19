<?php

namespace App\Controllers;

use App\Models\User;
use GuepardoSys\Core\Request;
use GuepardoSys\Core\Response;

/**
 * Users Controller
 */
class UsersController extends BaseController
{
    /**
     * Display all users
     */
    public function index(): string
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            // Optionally redirect or show error
            abort(403, 'Unauthorized');
        }
        try {
            $users = User::all();
            return view('users/index', ['users' => $users]);
        } catch (\Exception $e) {
            return view('errors/database', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Display a specific user
     */
    public function show(Request $request): string
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            // Optionally redirect or show error
            abort(403, 'Unauthorized');
        }
        $id = $request->getRouteParam('id');

        try {
            $user = User::find($id);

            if (!$user) {
                return view('errors/404', ['message' => 'User not found']);
            }

            return view('users/show', ['user' => $user]);
        } catch (\Exception $e) {
            return view('errors/database', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Show form to create a new user
     */
    public function create(): string
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            // Optionally redirect or show error
            abort(403, 'Unauthorized');
        }
        return view('users/create');
    }

    /**
     * Store a new user
     */
    public function store(Request $request): string
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            // Optionally redirect or show error
            abort(403, 'Unauthorized');
        }
        $data = $request->all();

        // Validate data
        $errors = User::validate($data);

        if (!empty($errors)) {
            return view('users/create', ['errors' => $errors, 'old' => $data]);
        }

        try {
            $user = User::create($data);
            return view('users/show', ['user' => $user, 'success' => 'User created successfully']);
        } catch (\Exception $e) {
            return view('users/create', ['errors' => ['general' => $e->getMessage()], 'old' => $data]);
        }
    }

    /**
     * Show form to edit a user
     */
    public function edit(Request $request): string
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            // Optionally redirect or show error
            abort(403, 'Unauthorized');
        }
        $id = $request->getRouteParam('id');

        try {
            $user = User::find($id);

            if (!$user) {
                return view('errors/404', ['message' => 'User not found']);
            }

            return view('users/edit', ['user' => $user]);
        } catch (\Exception $e) {
            return view('errors/database', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update a user
     */
    public function update(Request $request): string
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            // Optionally redirect or show error
            abort(403, 'Unauthorized');
        }
        $id = $request->getRouteParam('id');
        $data = $request->all();

        try {
            $user = User::find($id);

            if (!$user) {
                return view('errors/404', ['message' => 'User not found']);
            }

            // Remove password from validation if not provided
            if (empty($data['password'])) {
                unset($data['password']);
            }

            // Basic validation (simplified for update)
            $errors = [];
            if (empty($data['name'])) {
                $errors['name'] = 'Name is required';
            }
            if (empty($data['email'])) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            }

            if (!empty($errors)) {
                return view('users/edit', ['user' => $user, 'errors' => $errors]);
            }

            $user->update($data);

            return view('users/show', ['user' => $user, 'success' => 'User updated successfully']);
        } catch (\Exception $e) {
            return view('users/edit', ['user' => $user ?? null, 'errors' => ['general' => $e->getMessage()]]);
        }
    }

    /**
     * Delete a user
     */
    public function delete(Request $request): string
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            // Optionally redirect or show error
            abort(403, 'Unauthorized');
        }
        $id = $request->getRouteParam('id');

        try {
            $user = User::find($id);

            if (!$user) {
                return view('errors/404', ['message' => 'User not found']);
            }

            $user->delete();

            return $this->index(); // Redirect to index with success message
        } catch (\Exception $e) {
            return view('errors/database', ['error' => $e->getMessage()]);
        }
    }
}
