<?php

namespace App\Controllers;

/**
 * Controller base com funcionalidades comuns
 */
class BaseController
{
    /**
     * Renderiza uma view
     */
    protected function view($viewName, $data = [])
    {
        // Extrai as variáveis para o escopo da view
        extract($data);
        
        // Caminho para o arquivo de view
        $viewPath = __DIR__ . '/../../resources/views/' . str_replace('.', '/', $viewName) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View não encontrada: $viewName");
        }
        
        // Inclui a view
        include $viewPath;
    }
    
    /**
     * Retorna resposta JSON
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Redireciona para uma URL
     */
    protected function redirect($url, $statusCode = 302)
    {
        http_response_code($statusCode);
        header("Location: $url");
        exit;
    }
    
    /**
     * Valida dados de entrada
     */
    protected function validate($data, $rules)
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = "O campo $field é obrigatório";
                continue;
            }
            
            if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = "O campo $field deve ser um email válido";
            }
            
            if (preg_match('/min:(\d+)/', $rule, $matches)) {
                $min = $matches[1];
                if (strlen($value) < $min) {
                    $errors[$field] = "O campo $field deve ter pelo menos $min caracteres";
                }
            }
            
            if (preg_match('/max:(\d+)/', $rule, $matches)) {
                $max = $matches[1];
                if (strlen($value) > $max) {
                    $errors[$field] = "O campo $field deve ter no máximo $max caracteres";
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Obtém dados do POST
     */
    protected function getPostData()
    {
        return $_POST;
    }
    
    /**
     * Obtém dados do GET
     */
    protected function getGetData()
    {
        return $_GET;
    }
    
    /**
     * Obtém parâmetro da URL
     */
    protected function getParam($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Inicia sessão se não estiver iniciada
     */
    protected function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Define uma mensagem flash
     */
    protected function setFlash($type, $message)
    {
        $this->startSession();
        $_SESSION['flash'][$type] = $message;
    }
    
    /**
     * Obtém e remove uma mensagem flash
     */
    protected function getFlash($type)
    {
        $this->startSession();
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    
    /**
     * Função helper para views acessarem mensagens flash com segurança
     */
    public static function getFlashMessage($type)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $message = $_SESSION['flash'][$type] ?? null;
        if ($message) {
            unset($_SESSION['flash'][$type]);
        }
        return $message;
    }
}
