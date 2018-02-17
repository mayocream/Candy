<?php
namespace Candy\Controller;

class Index {
    
    public function __construct() {}
    
    public function view($request, $response)
    {
        $response->setContent('aaaaaaaaaaaa');
        return $response;
    }
    
}