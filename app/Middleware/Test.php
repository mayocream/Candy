<?php
namespace Candy\Middleware;

use Candy\Core\Di;

class Test {
    
    public function before($response)
    {
        $response->setStatusCode(404);
        Di::set('response', $response);
    }
    
}