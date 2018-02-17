<?php
namespace Candy\Core;

class Router {
    
    protected static $_instance;
    protected static $_dispatcher;
    
    private static $_routes = [];
    private static $_middleware = [];
    private static $_vars = [];
    
    private function __construct() {}
    private function __clone() {}
    
    public static function newInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public static function group($prefix, $routes, $middleware = [])
    {
        foreach($routes as $route) {
            if(isset($route[3])) {
                if(isset($route[3]['before'])) {
                    if(!isset($middleware['before'])) $middleware['before'] = [];
                    array_push($middleware['before'], $route[3]['before']);
                    array_unique($middleware['before']);
                }
                if(isset($route[3]['after']))  {
                    if(!isset($middleware['after'])) $middleware['after'] = [];
                    array_push($middleware['after'], $route[3]['after']);
                    array_unique($middleware['after']);
                }      
            }
            self::add($route[0], $prefix.$route[1], $route[2], $middleware);
        }
    }
    
    public static function add($method, $path, $callback, $middleware = [])
    {
        self::$_routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback,
            'middleware' => $middleware
        ];
    }
    
    public static function init_dispatcher()
    {
        self::$_dispatcher = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) {
            foreach(self::$_routes as $route) {
                // Route Line
                if(!empty($route['middleware'])) {
                    $hanlder = $route['callback'].'::'.json_encode($route['middleware']);
                } else {
                    $hanlder = $route['callback'];
                }
                $r->addRoute($route['method'], $route['path'], $hanlder);
            }
        });
    }
    
    
    public static function dispatch()
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        
        $routeInfo = self::$_dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                throw new \Candy\Exception\NotFoundException;
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                throw new \Candy\Exception\MethodNotAllowedException;
                break;
            case \FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                self::call($handler, $vars);
                break;
        }
    }
    
    private static function call($handler, $vars)
    {
        // String -> Controller
        if(is_string($handler)) {
            $matches = explode('::', $handler);
        }
        // vars
        self::$_vars = $vars;
        // Middleware ava
        if(isset($matches[2])) {
            $middleware = json_decode($matches[2], true);
        }
        // Before Middleware
        self::middleware($middleware);
        // Call Controller
        $class = 'Candy\Controller\\'.$matches[0];
        $method = $matches[1];
        $response = self::reflection($class, $method);
        \Candy\Core\Di::set('response', $response);
        // After Middleware
        self::middleware($middleware, 'after');
    }
    
    private static function middleware($middleware, $sort = 'before')
    {
        if(isset($middleware[$sort])) {
            if(is_array($middleware[$sort])) {
                foreach($middleware[$sort] as $class) {
                    $class = 'Candy\Middleware\\'.$class;
                    self::reflection($class, $sort);
                }
            } else {
                $class = 'Candy\Middleware\\'.$middleware[$sort];
                self::reflection($class, $sort);
            }
        }
    }
    
    private static function reflection($class, $method)
    {
        $ReflectionMethod = new \ReflectionMethod($class, $method);
        $parameters = $ReflectionMethod->getParameters();
        // vars in array
        $services = ['request', 'response'];
        $params = [];
        foreach ($parameters as $parameter) {
            $name = $parameter->name;
            if(in_array($name, $services)) {
                $params[] = \Candy\Core\Di::get($name);
            } elseif (array_key_exists($name, self::$_vars)) {
                $params[] = self::$_vars[$name];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $params[] = $parameter->getDefaultValue();
            } else {
                $params[] = null;
            }
        }
        $object = new $class;
        //var_dump($method->name);
        return call_user_func_array([$object, $method], $params);
    }
    
    
}
