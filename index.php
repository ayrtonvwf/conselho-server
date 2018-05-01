<?php

require __DIR__.'/vendor/autoload.php';

const BASE_PATH = __DIR__;

use Dotenv\Dotenv;
use MiladRahimi\PHPRouter\Router;
use MiladRahimi\PHPRouter\Exceptions\HttpError;

date_default_timezone_set('UTC');
header('Content-Type: application/json; charset=UTF-8');

if (($_SERVER['HTTP_ACCEPT'] ?? '') != 'application/json') {
    http_response_code(406);
    die(json_encode('WRONG_HTTP_ACCEPT'));
}

if (($_SERVER['CONTENT_TYPE'] ?? '') != 'application/json') {
    http_response_code(400);
    die(json_encode('WRONG_CONTENT_TYPE'));
}

$dotenv = new Dotenv(__DIR__);
$dotenv->load();
$router = new Router(getenv('BASE_PATH'));

$router->map('GET', '/council[/]*', 'Conselho\\Controllers\\Council@get', 'Conselho\\Auth@check');
$router->map('POST', '/council[/]*', 'Conselho\\Controllers\\Council@post', 'Conselho\\Auth@check');
$router->map('PUT', '/council[/]*', 'Conselho\\Controllers\\Council@put', 'Conselho\\Auth@check');
$router->map('DELETE', '/council[/]*', 'Conselho\\Controllers\\Council@delete', 'Conselho\\Auth@check');

$router->map('GET', '/evaluation[/]*', 'Conselho\\Controllers\\Evaluation@get', 'Conselho\\Auth@check');
$router->map('POST', '/evaluation[/]*', 'Conselho\\Controllers\\Evaluation@post', 'Conselho\\Auth@check');
$router->map('PUT', '/evaluation[/]*', 'Conselho\\Controllers\\Evaluation@put', 'Conselho\\Auth@check');
$router->map('DELETE', '/evaluation[/]*', 'Conselho\\Controllers\\Evaluation@delete', 'Conselho\\Auth@check');

$router->map('GET', '/grade[/]*', 'Conselho\\Controllers\\Grade@get', 'Conselho\\Auth@check');
$router->map('POST', '/grade[/]*', 'Conselho\\Controllers\\Grade@post', 'Conselho\\Auth@check');
$router->map('PUT', '/grade[/]*', 'Conselho\\Controllers\\Grade@put', 'Conselho\\Auth@check');
$router->map('DELETE', '/grade[/]*', 'Conselho\\Controllers\\Grade@delete', 'Conselho\\Auth@check');

$router->map('GET', '/student[/]*', 'Conselho\\Controllers\\Student@get', 'Conselho\\Auth@check');
$router->map('POST', '/student[/]*', 'Conselho\\Controllers\\Student@post', 'Conselho\\Auth@check');
$router->map('PUT', '/student[/]*', 'Conselho\\Controllers\\Student@put', 'Conselho\\Auth@check');
$router->map('DELETE', '/student[/]*', 'Conselho\\Controllers\\Student@delete', 'Conselho\\Auth@check');

$router->map('GET', '/subject[/]*', 'Conselho\\Controllers\\Subject@get', 'Conselho\\Auth@check');
$router->map('POST', '/subject[/]*', 'Conselho\\Controllers\\Subject@post', 'Conselho\\Auth@check');
$router->map('PUT', '/subject[/]*', 'Conselho\\Controllers\\Subject@put', 'Conselho\\Auth@check');
$router->map('DELETE', '/subject[/]*', 'Conselho\\Controllers\\Subject@delete', 'Conselho\\Auth@check');

$router->map('GET', '/user_token[/]*', 'Conselho\\Controllers\\UserToken@get', 'Conselho\\Auth@check');
$router->map('POST', '/user_token[/]*', 'Conselho\\Controllers\\UserToken@post');
$router->map('DELETE', '/user_token[/]*', 'Conselho\\Controllers\\UserToken@delete', 'Conselho\\Auth@check');

$router->map('GET', '/topic[/]*', 'Conselho\\Controllers\\Topic@get', 'Conselho\\Auth@check');
$router->map('POST', '/topic[/]*', 'Conselho\\Controllers\\Topic@post', 'Conselho\\Auth@check');
$router->map('PUT', '/topic[/]*', 'Conselho\\Controllers\\Topic@put', 'Conselho\\Auth@check');
$router->map('DELETE', '/topic[/]*', 'Conselho\\Controllers\\Topic@delete', 'Conselho\\Auth@check');

$router->map('GET', '/user[/]*', 'Conselho\\Controllers\\User@get', 'Conselho\\Auth@check');
$router->map('POST', '/user[/]*', 'Conselho\\Controllers\\User@post');
$router->map('PUT', '/user[/]*', 'Conselho\\Controllers\\User@put', 'Conselho\\Auth@check');
$router->map('DELETE', '/user[/]*', 'Conselho\\Controllers\\User@delete', 'Conselho\\Auth@check');

$router->map('GET', '/role[/]*', 'Conselho\\Controllers\\Role@get', 'Conselho\\Auth@check');
$router->map('POST', '/role[/]*', 'Conselho\\Controllers\\Role@post', 'Conselho\\Auth@check');
$router->map('DELETE', '/role[/]*', 'Conselho\\Controllers\\Role@delete', 'Conselho\\Auth@check');

$router->map('GET', '/school[/]*', 'Conselho\\Controllers\\School@get', 'Conselho\\Auth@check');
$router->map('GET', '/user_type[/]*', 'Conselho\\Controllers\\UserType@get', 'Conselho\\Auth@check');
$router->map('GET', '/topic_type[/]*', 'Conselho\\Controllers\\TopicType@get', 'Conselho\\Auth@check');

if (getenv('env') == 'dev') {
    $router->get('/dev/playground[/]*', function() {});
}

try {
    $router->dispatch();
} catch(HttpError $error) {
    if ($error->getMessage() == "404") {
        $router->publish("Error 404! Not found!");
    }
    //...

} catch(Exception $e) {
    // Log details...
    $router->publish("Sorry, there is an internal error, we will fix it asap!\n\n");
    if (getenv('ENV') == 'dev') {
        print_r($e);
    }
}