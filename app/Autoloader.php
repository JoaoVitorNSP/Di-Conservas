<?php

/**
 * Autoloader customizado para carregar classes automaticamente
 */
class Autoloader
{
    private static $instance = null;
    private $baseDir;
    private $classMap = [];
    
    private function __construct()
    {
        $this->baseDir = dirname(__DIR__);
        $this->registerAutoloader();
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function registerAutoloader()
    {
        spl_autoload_register([$this, 'loadClass']);
    }
    
    public function loadClass($className)
    {
        // Remove barras invertidas duplas
        $className = ltrim($className, '\\');
        
        // Verifica se a classe está no mapeamento
        if (isset($this->classMap[$className])) {
            require_once $this->classMap[$className];
            return true;
        }
        
        // Converte namespace para caminho de arquivo
        $file = $this->baseDir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        
        // Tentativa alternativa para classes do Router
        if (strpos($className, 'Router') !== false) {
            $routerFile = $this->baseDir . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'Router.php';
            if (file_exists($routerFile)) {
                require_once $routerFile;
                return true;
            }
        }
        
        return false;
    }
    
    public function addClassMap($className, $filePath)
    {
        $this->classMap[$className] = $filePath;
    }
}

// Inicializa o autoloader
Autoloader::getInstance();
