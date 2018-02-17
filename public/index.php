<?php
require "./../vendor/autoload.php";

define("APP_PATH", "./../app");

$app = new \Candy\Core\App;

require APP_PATH."/routes.php";

$app->run();
