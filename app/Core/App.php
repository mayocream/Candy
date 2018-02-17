<?php
namespace Candy\Core;

use Candy\Core\Di;
use Candy\Core\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Candy\Exception\MethodNotAllowedException;
use Candy\Exception\NotFoundException;


class App {
    
    protected $_intance;
    
    public function __construct() {
        $this->DI();
        Router::newInstance();
        $this->setRequest();
        $this->setResponse();
        return $this->DI();
    }
    
    public function DI()
    {
        return Di::instance();
    }
    
    private function setRequest() {
        Di::set('request', function(){
            return Request::createFromGlobals();
        });
    }
    
    private function setResponse() {
        Di::set('response', function(){
            return new Response();
        });
    }
    
    public function run()
    {
        try {
            Router::init_dispatcher();
            Router::dispatch();
            Di::get('response')->send();
            $this->end();
        } 
        
        catch(NotFoundException $e) {
            $msg = '404';
        } catch(MethodNotAllowedException $e) {
            $msg = '405';
        } catch(\Throwable $e) {
            $msg = $e->getMessage();
        }
        Di::get('response')->setContent($msg);
        Di::get('response')->send();
    }
    
    private function exception()
    {
        echo 'ERRRRRRRR';
    }
    
    public function end()
    {
        exit;
    }
    
}