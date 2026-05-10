<?php

declare(strict_types=1);

namespace Core;

abstract class Controller
{
    /**
     * Render a view file inside the main layout.
     *
     * @param string               $view   Relative path under views/ (e.g. 'products/index')
     * @param array<string, mixed> $data   Variables to extract into the view
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $viewFile   = BASE_PATH . '/views/' . $view . '.php';
        $layoutFile = BASE_PATH . '/views/layouts/main.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: $viewFile");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require $layoutFile;
    }

    /**
     * Output a JSON response and terminate.
     *
     * @param mixed $data
     */
    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    /**
     * Retrieve and clear the flash message.
     *
     * @return array{type: string, message: string}|null
     */
    public static function getFlash(): ?array
    {
        if (!empty($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}
