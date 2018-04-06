<?php
namespace Conselho;

class Router extends \MiladRahimi\PHPRouter\Router {
    private const DEFAULT_METHODS = ['GET', 'POST', 'PUT', 'DELETE'];

    public function __construct(string $base_path = '', array $routes) {
        if ($base_path && $base_path[0] != '/') {
            $base_path = "/$base_path";
        }
        
        parent::__construct($base_path);

        foreach ($routes as $key => $value) {
            if (is_numeric($key)) {
                $path = $value;
                $methods = $this::DEFAULT_METHODS;
            } else {
                $path = $key;
                $methods = $value; 
            }

            $class_name = $this->makeClassName($path);
            foreach ($methods as $method) {
                $function_name = strtolower($method);
                $this->map($method, "/$path"."[/]*", "Conselho\\Controllers\\$class_name@$function_name");
            }
        }
    }

    public function dispatch() : void {
        parent::dispatch();
    }

    private function makeClassName(string $path) : string {
        return str_replace(' ', '', ucfirst(str_replace('_', ' ', $path)));
    }
}