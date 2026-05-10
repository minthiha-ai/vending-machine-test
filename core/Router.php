<?php

declare(strict_types=1);

namespace Core;

use ReflectionClass;
use ReflectionMethod;

class Router
{
    /** @var array<int, array{method: string, pattern: string, params: list<string>, controller: string, action: string}> */
    private array $routes = [];

    /**
     * Register all #[Route] attributes found on the given controller classes.
     *
     * @param list<class-string> $controllers
     */
    public function registerControllers(array $controllers): void
    {
        foreach ($controllers as $controllerClass) {
            $reflector = new ReflectionClass($controllerClass);
            foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $attributes = $method->getAttributes(Route::class);
                foreach ($attributes as $attribute) {
                    /** @var Route $route */
                    $route = $attribute->newInstance();
                    [$pattern, $params] = $this->compilePath($route->path);
                    $this->routes[] = [
                        'method'     => strtoupper($route->method),
                        'pattern'    => $pattern,
                        'params'     => $params,
                        'controller' => $controllerClass,
                        'action'     => $method->getName(),
                    ];
                }
            }
        }
    }

    public function dispatch(string $httpMethod, string $uri): void
    {
        $uri        = '/' . trim($uri, '/');
        $httpMethod = strtoupper($httpMethod);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $httpMethod) {
                continue;
            }
            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }

            $args = [];
            foreach ($route['params'] as $name) {
                $args[] = $matches[$name];
            }

            $controller = new $route['controller']();
            $controller->{$route['action']}(...$args);
            return;
        }

        http_response_code(404);
        if (file_exists(BASE_PATH . '/views/errors/404.php')) {
            require BASE_PATH . '/views/errors/404.php';
        } else {
            echo '<h1>404 Not Found</h1>';
        }
    }

    /**
     * Convert /path/{id}/edit → regex + param names list.
     *
     * @return array{0: string, 1: list<string>}
     */
    private function compilePath(string $path): array
    {
        $params  = [];
        $pattern = preg_replace_callback(
            '/\{(\w+)\}/',
            static function (array $m) use (&$params): string {
                $params[] = $m[1];
                return '(?P<' . $m[1] . '>[^/]+)';
            },
            $path
        );
        $pattern = '#^' . $pattern . '$#';
        return [$pattern, $params];
    }
}
