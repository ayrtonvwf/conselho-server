<?php

require __DIR__.'/vendor/autoload.php';

use Conselho\Router;
use Dotenv\Dotenv;

$dotenv = new Dotenv(__DIR__);
$dotenv->load();

$routes = [
    'council',
    'evaluation',
    'grade',
    'grade_has_subject',
    'grade_obs',
    'medical_report',
    'permission',
    'student',
    'token',
    'topic',
    'topic_type',
    'user',
    'user_type',
    'user_type_permission'
];

$router = new Router(getenv('BASE_PATH'), $routes);

try {
    $router->dispatch();
} catch(HttpError $error) {
    if ($error->getMessage() == "404") {
        $router->publish("Error 404! Not found!");
    }
    //...

} catch(Exception $e) {
    // Log details...
    $router->publish("Sorry, there is an internal error, we will fix it asap!");
}