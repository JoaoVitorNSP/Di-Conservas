<?php

/**
 * Sistema simples de roteamento
 */
class Router
{
    private $routes = [];
    private $params = [];
    
    /**
     * Adiciona uma rota GET
     */
    public function get($pattern, $controller, $action)
    {
        $this->addRoute('GET', $pattern, $controller, $action);
    }
    
    /**
     * Adiciona uma rota POST
     */
    public function post($pattern, $controller, $action)
    {
        $this->addRoute('POST', $pattern, $controller, $action);
    }
    
    /**
     * Adiciona uma rota para qualquer método
     */
    public function any($pattern, $controller, $action)
    {
        $this->addRoute('*', $pattern, $controller, $action);
    }
    
    /**
     * Adiciona uma rota
     */
    private function addRoute($method, $pattern, $controller, $action)
    {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    /**
     * Resolve a rota atual
     */
    public function resolve()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== '*' && $route['method'] !== $method) {
                continue;
            }
            
            if ($this->matchRoute($route['pattern'], $uri)) {
                $this->callController($route['controller'], $route['action']);
                return;
            }
        }
        
        // Rota não encontrada
        http_response_code(404);
        echo "404 - Página não encontrada";
    }
    
    /**
     * Verifica se a URL corresponde ao padrão da rota
     */
    private function matchRoute($pattern, $uri)
    {
        // Converte padrões como {id} em expressões regulares
        $pattern = preg_replace('/\{(\w+)\}/', '(\d+)', $pattern);
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = '/^' . $pattern . '$/';
        
        if (preg_match($pattern, $uri, $matches)) {
            // Remove o primeiro elemento (string completa)
            array_shift($matches);
            $this->params = $matches;
            return true;
        }
        
        return false;
    }
    
    /**
     * Chama o controller e action
     */
    private function callController($controllerName, $action)
    {
        $controllerClass = "App\\Controllers\\{$controllerName}";
        
        // Tentativa de carregar manualmente se a classe não existir
        if (!class_exists($controllerClass)) {
            // Primeiro, carrega o BaseController se necessário
            $baseControllerFile = dirname(__DIR__) . "/app/Controllers/BaseController.php";
            if (!class_exists('App\\Controllers\\BaseController') && file_exists($baseControllerFile)) {
                require_once $baseControllerFile;
            }
            
            // Depois carrega o controller específico
            $controllerFile = dirname(__DIR__) . "/app/Controllers/{$controllerName}.php";
            
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
            }
            
            // Verifica novamente se a classe existe
            if (!class_exists($controllerClass)) {
                throw new Exception("Controller não encontrado: $controllerClass (arquivo: $controllerFile)");
            }
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $action)) {
            throw new Exception("Action não encontrada: $action no controller $controllerClass");
        }
        
        // Chama a action com os parâmetros da URL
        call_user_func_array([$controller, $action], $this->params);
    }
}
