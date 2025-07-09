<?php

namespace App\Controllers;

use App\Models\User;
use GuepardoSys\Core\Request;

/**
 * AuthController
 */
class AuthController extends BaseController
{
    /**
     * Show login form
     */
    public function showLogin(): string
    {
        if ($this->isAuthenticated()) {
            header('Location: /dashboard');
            exit;
        }

        return view('auth.login', [
            'title' => 'Login',
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old'] ?? []
        ]);
    }

    /**
     * Handle login form submission
     */
    public function login(Request $request): void
    {
        if ($this->isAuthenticated()) {
            header('Location: /dashboard');
            exit;
        }

        $email = $request->input('email', '');
        $password = $request->input('password', '');

        $errors = [];

        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = ['email' => $email];
            header('Location: /login');
            exit;
        }

        $user = User::findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            $_SESSION['errors'] = ['login' => 'Invalid email or password'];
            $_SESSION['old'] = ['email' => $email];
            header('Location: /login');
            exit;
        }

        $this->loginUser($user);
        unset($_SESSION['errors'], $_SESSION['old']);

        $redirectTo = $_SESSION['intended'] ?? '/dashboard';
        unset($_SESSION['intended']);

        header('Location: ' . $redirectTo);
        exit;
    }

    /**
     * Show registration form
     */
    public function showRegister(): string
    {
        if ($this->isAuthenticated()) {
            header('Location: /dashboard');
            exit;
        }

        return view('auth.register', [
            'title' => 'Register',
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old'] ?? []
        ]);
    }

    /**
     * Handle registration form submission
     */
    public function register(Request $request): void
    {
        if ($this->isAuthenticated()) {
            header('Location: /dashboard');
            exit;
        }

        $data = [
            'name' => $request->input('name', ''),
            'email' => $request->input('email', ''),
            'password' => $request->input('password', ''),
            'password_confirmation' => $request->input('password_confirmation', '')
        ];

        $errors = User::validate($data);

        if ($data['password'] !== $data['password_confirmation']) {
            $errors['password_confirmation'] = 'Password confirmation does not match';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = [
                'name' => $data['name'],
                'email' => $data['email']
            ];
            header('Location: /register');
            exit;
        }

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password']
            ]);

            $this->loginUser($user);
            unset($_SESSION['errors'], $_SESSION['old']);

            header('Location: /dashboard');
            exit;
        } catch (\Exception $e) {
            $_SESSION['errors'] = ['general' => 'Registration failed. Please try again.'];
            $_SESSION['old'] = [
                'name' => $data['name'],
                'email' => $data['email']
            ];
            header('Location: /register');
            exit;
        }
    }

    /**
     * Handle logout
     */
    public function logout(): void
    {
        $this->logoutUser();
        header('Location: /login');
        exit;
    }

    /**
     * Dashboard page
     */
    public function dashboard(): string
    {
        if (!$this->isAuthenticated()) {
            $_SESSION['intended'] = '/dashboard';
            header('Location: /login');
            exit;
        }

        $user = $this->getAuthenticatedUser();

        return view('auth.dashboard', [
            'title' => 'Dashboard',
            'user' => $user
        ]);
    }

    /**
     * Login a user
     */
    protected function loginUser(User $user): void
    {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_name'] = $user->getDisplayName();
        $_SESSION['user_email'] = $user->email;
        session_regenerate_id(true);
    }

    /**
     * Logout current user
     */
    protected function logoutUser(): void
    {
        unset(
            $_SESSION['user_id'],
            $_SESSION['user_logged_in'],
            $_SESSION['user_name'],
            $_SESSION['user_email']
        );
        session_regenerate_id(true);
    }

    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
    }

    /**
     * Get authenticated user
     */
    protected function getAuthenticatedUser(): ?User
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return null;
        }

        return User::find($userId);
    }
}
