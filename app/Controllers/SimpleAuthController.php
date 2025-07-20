<?php

namespace App\Controllers;

use App\Models\User;
use GuepardoSys\Core\Request;

/**
 * Simple AuthController without views
 */
class SimpleAuthController extends BaseController
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

        return '<!DOCTYPE html>
<html>
<head>
    <title>Login - GuepardoSys</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 400px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #005a87; }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: #007cba; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form method="POST" action="/login">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <div class="links">
            <a href="/register">Need an account? Register here</a>
        </div>
    </div>
</body>
</html>';
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

        $user = User::findByEmail($email);

        if ($user && $user->verifyPassword($password)) {
            $this->loginUser($user);
            header('Location: /dashboard');
            exit;
        }

        header('Location: /login?error=1');
        exit;
    }

    /**
     * Dashboard page
     */
    public function dashboard(): string
    {
        if (!$this->isAuthenticated()) {
            header('Location: /login');
            exit;
        }

        $user = $this->getAuthenticatedUser();

        // Proteção contra usuário null
        if (!$user) {
            return '<!DOCTYPE html>
<html><head><title>Dashboard - GuepardoSys</title></head><body><div class="container"><h2>Usuário não encontrado ou sessão expirada.</h2><a href="/login">Fazer login novamente</a></div></body></html>';
        }

        return '<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - GuepardoSys</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .header { background: #007cba; color: white; padding: 20px; }
        .container { max-width: 800px; margin: 40px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { margin: 0; }
        .user-info { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .btn { display: inline-block; padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 4px; }
        .btn:hover { background: #c82333; }
        .welcome { color: #333; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>GuepardoSys Dashboard</h1>
    </div>
    <div class="container">
        <div class="welcome">
            <h2>Welcome, ' . htmlspecialchars($user->name) . '!</h2>
        </div>
        <div class="user-info">
            <h3>Account Information</h3>
            <p><strong>Name:</strong> ' . htmlspecialchars($user->name) . '</p>
            <p><strong>Email:</strong> ' . htmlspecialchars($user->email) . '</p>
            <p><strong>Member since:</strong> ' . date('F j, Y', strtotime($user->created_at)) . '</p>
        </div>
        <a href="/logout" class="btn">Logout</a>
    </div>
</body>
</html>';
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

    /**
     * Show registration form
     */
    public function showRegister(): string
    {
        if ($this->isAuthenticated()) {
            header('Location: /dashboard');
            exit;
        }

        return '<!DOCTYPE html>
<html>
<head>
    <title>Register - GuepardoSys</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 400px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #005a87; }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: #007cba; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create Account</h2>
        <form method="POST" action="/register">
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Confirm Password:</label>
                <input type="password" name="password_confirmation" required>
            </div>
            <button type="submit">Create Account</button>
        </form>
        <div class="links">
            <a href="/login">Already have an account? Login here</a>
        </div>
    </div>
</body>
</html>';
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
            echo 'Registration failed: ' . implode(', ', $errors);
            return;
        }

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password']
            ]);

            $this->loginUser($user);
            header('Location: /dashboard');
            exit;
        } catch (\Exception $e) {
            echo 'Registration failed: ' . $e->getMessage();
        }
    }
}
