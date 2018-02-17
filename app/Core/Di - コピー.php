<?php
namespace Candy\Core;

class Di {
    
    protected static $_instance;
    
    private function __construct() {}
    private function __clone() {}
    
    private $_bindings = [];
    private $_callbacks = [];
    
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function get($name)
    {
        if (isset($this->_callbacks[$name])) {
            return $this->_callbacks[$name];
        }
        // Closure
        $obj = $this->new($name);
        $this->_callbacks[$name] = $obj;
        return $obj;
    }
    
    public function new($name)
    {
        $callback = $this->_bindings[$name];
        $obj = call_user_func($callback);
        return $obj;
    }
    
    public function has($name)
    {
        return isset($this->_bindings[$name]) or isset($this->_callbacks[$name]);
    }
    
    public function remove($name)
    {
        unset($this->_bindings[$name], $this->_callbacks[$name]);
    }
    
    public function set($name, $callback)
    {
        if($callback instanceof \Closure) {
            $this->_bindings[$name] = $callback;
        } else {
            $this->_callbacks[$name] = $callback;
        }
    }
    
}