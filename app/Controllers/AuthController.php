<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Middleware;
use Core\Route;
use Core\JWT;
use App\Models\User;

class AuthController extends Controller
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    #[Route('/login', method: 'GET')]
    public function loginForm(): void
    {
        if (!empty($_SESSION['user'])) {
            $this->redirect('/');
        }
        $this->renderLogin(['errors' => []]);
    }

    #[Route('/login', method: 'POST')]
    public function login(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors   = [];

        if ($username === '') {
            $errors['username'] = 'Username is required.';
        }
        if ($password === '') {
            $errors['password'] = 'Password is required.';
        }

        if (empty($errors)) {
            $user = $this->users->findByUsername($username);
            if (!$user || !$this->users->verifyPassword($password, $user['password'])) {
                $errors['general'] = 'Invalid username or password.';
            }
        }

        if (!empty($errors)) {
            $this->renderLogin(['errors' => $errors, 'old_username' => $username]);
            return;
        }

        /** @var array $user */
        $_SESSION['user'] = [
            'id'       => (int) $user['id'],
            'username' => $user['username'],
            'role'     => $user['role'],
        ];

        $this->flash('success', 'Welcome back, ' . e($user['username']) . '!');
        $this->redirect('/');
    }

    #[Route('/logout', method: 'GET')]
    public function logout(): void
    {
        session_destroy();
        $this->redirect('/login');
    }

    #[Route('/api/token', method: 'POST')]
    public function apiToken(): void
    {
        $body     = json_decode(file_get_contents('php://input'), true) ?? [];
        $username = trim($body['username'] ?? '');
        $password = $body['password'] ?? '';

        if ($username === '' || $password === '') {
            $this->json(['error' => 'username and password are required'], 422);
        }

        $user = $this->users->findByUsername($username);
        if (!$user || !$this->users->verifyPassword($password, $user['password'])) {
            $this->json(['error' => 'Invalid credentials'], 401);
        }

        $token = JWT::generate([
            'sub'      => $user['id'],
            'username' => $user['username'],
            'role'     => $user['role'],
        ]);

        $this->json(['token' => $token]);
    }

    /**
     * Render the login page directly — it is a standalone HTML file
     * and must NOT be wrapped inside the main layout.
     *
     * @param array<string, mixed> $data
     */
    private function renderLogin(array $data): void
    {
        extract($data, EXTR_SKIP);
        require BASE_PATH . '/views/auth/login.php';
    }
}
