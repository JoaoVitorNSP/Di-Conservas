<?php
declare(strict_types=1);

// Roteador simples: associa método + endereço a um controller.
class Router
{
    private array $routes = [];

    public function get(string $pattern, string $controller, string $action): void
    {
        $this->routes[] = ['GET', $pattern, $controller, $action];
    }

    public function post(string $pattern, string $controller, string $action): void
    {
        $this->routes[] = ['POST', $pattern, $controller, $action];
    }

    public function resolve(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] === 'HEAD' ? 'GET' : $_SERVER['REQUEST_METHOD'];
        $uri = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = $uri === '/' ? '/' : rtrim($uri, '/');
        $pathExists = false;

        foreach ($this->routes as [$routeMethod, $pattern, $controller, $action]) {
            $regex = '#^' . str_replace('{id}', '(\d+)', $pattern) . '$#';
            if (!preg_match($regex, $uri, $matches)) {
                continue;
            }
            $pathExists = true;
            if ($routeMethod !== $method) {
                continue;
            }
            $class = "App\\Controllers\\{$controller}";
            array_shift($matches);
            (new $class())->$action(...array_map('intval', $matches));
            return;
        }

        http_response_code($pathExists ? 405 : 404);
        $title = 'Página não encontrada';
        include ROOT_PATH . '/resources/views/errors/404.php';
    }
}
