<?php
use Candy\Core\Router;

Router::add('GET', '/', 'Index::view', ['before' => 'Test']);

Router::group('/hello', [
    ['GET', '', 'Index::view'],
    ['GET', '/', 'Index::view', ['before' => 'Test']]
]);