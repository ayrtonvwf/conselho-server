<?php

require __DIR__.'/vendor/autoload.php';

const BASE_PATH = __DIR__;

use Dotenv\Dotenv;
use MiladRahimi\PHPRouter\Router;
use MiladRahimi\PHPRouter\Exceptions\HttpError;

date_default_timezone_set('UTC');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Max-Age: 86400'); // cache for 1 day
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PATCH, DELETE');
if (!empty($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
    header("Access-Control-Allow-Headers: $_SERVER[HTTP_ACCESS_CONTROL_REQUEST_HEADERS]");
}

$accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'OPTIONS' && strpos($accept, 'application/json') !== 0) {
    http_response_code(406);
    exit;
}

$dotenv = new Dotenv(__DIR__);
$dotenv->load();

$controller_prefix = 'Conselho\\Controllers\\';
$router = new Router(getenv('BASE_PATH'));
$router->define('id', Router::NUMERIC);
$router->group('Conselho\\Auth@check', function(Router $router) use ($controller_prefix) {
    $router->map('GET', '/council', "{$controller_prefix}Council@get");
    $router->map('POST', '/council', "{$controller_prefix}Council@post");
    $router->map('PATCH', '/council/{id}', "{$controller_prefix}Council@patch");
    $router->map('DELETE', '/council/{id}', "{$controller_prefix}Council@delete");
    
    $router->map('GET', '/council_grade', "{$controller_prefix}CouncilGrade@get");
    $router->map('POST', '/council_grade', "{$controller_prefix}CouncilGrade@post");
    $router->map('DELETE', '/council_grade/{id}', "{$controller_prefix}CouncilGrade@delete");
    
    $router->map('GET', '/council_topic', "{$controller_prefix}CouncilTopic@get");
    $router->map('POST', '/council_topic', "{$controller_prefix}CouncilTopic@post");
    $router->map('DELETE', '/council_topic/{id}', "{$controller_prefix}CouncilTopic@delete");
    
    $router->map('GET', '/evaluation', "{$controller_prefix}Evaluation@get");
    $router->map('POST', '/evaluation', "{$controller_prefix}Evaluation@post");
    $router->map('PATCH', '/evaluation/{id}', "{$controller_prefix}Evaluation@patch");
    $router->map('DELETE', '/evaluation/{id}', "{$controller_prefix}Evaluation@delete");
    
    $router->map('GET', '/grade', "{$controller_prefix}Grade@get");
    $router->map('POST', '/grade', "{$controller_prefix}Grade@post");
    $router->map('PATCH', '/grade/{id}', "{$controller_prefix}Grade@patch");
    $router->map('DELETE', '/grade/{id}', "{$controller_prefix}Grade@delete");
    
    $router->map('GET', '/grade_observation', "{$controller_prefix}GradeObservation@get");
    $router->map('POST', '/grade_observation', "{$controller_prefix}GradeObservation@post");
    $router->map('PATCH', '/grade_observation/{id}', "{$controller_prefix}GradeObservation@patch");
    $router->map('DELETE', '/grade_observation/{id}', "{$controller_prefix}GradeObservation@delete");
    
    $router->map('GET', '/grade_subject', "{$controller_prefix}GradeSubject@get");
    $router->map('POST', '/grade_subject', "{$controller_prefix}GradeSubject@post");
    $router->map('DELETE', '/grade_subject/{id}', "{$controller_prefix}GradeSubject@delete");
    
    $router->map('GET', '/medical_report', "{$controller_prefix}MedicalReport@get");
    $router->map('POST', '/medical_report', "{$controller_prefix}MedicalReport@post");
    $router->map('PATCH', '/medical_report/{id}', "{$controller_prefix}MedicalReport@patch");
    $router->map('DELETE', '/medical_report/{id}', "{$controller_prefix}MedicalReport@delete");
    
    $router->map('GET', '/medical_report_subject', "{$controller_prefix}MedicalReportSubject@get");
    $router->map('POST', '/medical_report_subject', "{$controller_prefix}MedicalReportSubject@post");
    $router->map('DELETE', '/medical_report_subject/{id}', "{$controller_prefix}MedicalReportSubject@delete");
    
    $router->map('GET', '/permission', "{$controller_prefix}Permission@get");

    $router->map('GET', '/role', "{$controller_prefix}Role@get");
    $router->map('POST', '/role', "{$controller_prefix}Role@post");
    $router->map('PATCH', '/role/{id}', "{$controller_prefix}Role@patch");
    $router->map('DELETE', '/role/{id}', "{$controller_prefix}Role@delete");

    $router->map('GET', '/role_type', "{$controller_prefix}RoleType@get");
    $router->map('POST', '/role_type', "{$controller_prefix}RoleType@post");
    $router->map('PATCH', '/role_type/{id}', "{$controller_prefix}RoleType@patch");
    $router->map('DELETE', '/role_type/{id}', "{$controller_prefix}RoleType@delete");

    $router->map('GET', '/role_type_permission', "{$controller_prefix}RoleTypePermission@get");
    $router->map('POST', '/role_type_permission', "{$controller_prefix}RoleTypePermission@post");
    $router->map('DELETE', '/role_type_permission/{id}', "{$controller_prefix}RoleTypePermission@delete");

    $router->map('GET', '/school', "{$controller_prefix}School@get");
    
    $router->map('GET', '/student', "{$controller_prefix}Student@get");
    $router->map('POST', '/student', "{$controller_prefix}Student@post");
    $router->map('PATCH', '/student/{id}', "{$controller_prefix}Student@patch");
    $router->map('DELETE', '/student/{id}', "{$controller_prefix}Student@delete");
    
    $router->map('GET', '/student_grade', "{$controller_prefix}StudentGrade@get");
    $router->map('POST', '/student_grade', "{$controller_prefix}StudentGrade@post");
    $router->map('PATCH', '/student_grade/{id}', "{$controller_prefix}StudentGrade@patch");
    $router->map('DELETE', '/student_grade/{id}', "{$controller_prefix}StudentGrade@delete");
    
    $router->map('GET', '/student_observation', "{$controller_prefix}StudentObservation@get");
    $router->map('POST', '/student_observation', "{$controller_prefix}StudentObservation@post");
    $router->map('PATCH', '/student_observation/{id}', "{$controller_prefix}StudentObservation@patch");
    $router->map('DELETE', '/student_observation/{id}', "{$controller_prefix}StudentObservation@delete");

    $router->map('GET', '/subject', "{$controller_prefix}Subject@get");
    $router->map('POST', '/subject', "{$controller_prefix}Subject@post");
    $router->map('PATCH', '/subject/{id}', "{$controller_prefix}Subject@patch");
    $router->map('DELETE', '/subject/{id}', "{$controller_prefix}Subject@delete");

    $router->map('GET', '/teacher', "{$controller_prefix}Teacher@get");
    $router->map('POST', '/teacher', "{$controller_prefix}Teacher@post");
    $router->map('PATCH', '/teacher/{id}', "{$controller_prefix}Teacher@patch");
    $router->map('DELETE', '/teacher/{id}', "{$controller_prefix}Teacher@delete");

    $router->map('GET', '/teacher_request', "{$controller_prefix}TeacherRequest@get");
    $router->map('POST', '/teacher_request', "{$controller_prefix}TeacherRequest@post");
    $router->map('DELETE', '/teacher_request/{id}', "{$controller_prefix}TeacherRequest@delete");

    $router->map('GET', '/topic', "{$controller_prefix}Topic@get");
    $router->map('POST', '/topic', "{$controller_prefix}Topic@post");
    $router->map('PATCH', '/topic/{id}', "{$controller_prefix}Topic@patch");
    $router->map('DELETE', '/topic/{id}', "{$controller_prefix}Topic@delete");

    $router->map('GET', '/topic_option', "{$controller_prefix}TopicOption@get");
    $router->map('POST', '/topic_option', "{$controller_prefix}TopicOption@post");
    $router->map('PATCH', '/topic_option/{id}', "{$controller_prefix}TopicOption@patch");
    $router->map('DELETE', '/topic_option/{id}', "{$controller_prefix}TopicOption@delete");

    $router->map('GET', '/user', "{$controller_prefix}User@get");
    $router->map('PATCH', '/user', "{$controller_prefix}User@patch");
    $router->map('DELETE', '/user', "{$controller_prefix}User@delete");

    $router->map('GET', '/user_token', "{$controller_prefix}UserToken@get");
    $router->map('PATCH', '/user_token', "{$controller_prefix}UserToken@patch");
    $router->map('DELETE', '/user_token', "{$controller_prefix}UserToken@delete");
});
$router->map('POST', '/user[/]*', "{$controller_prefix}User@post");
$router->map('POST', '/user_token[/]*', "{$controller_prefix}UserToken@post");
$router->map('OPTIONS', '*', function() {});

try {
    $router->dispatch();
} catch(Exception $e) {
    $error_code = $e->getMessage() == '404' ? 404 : 500;
    if (getenv('ENV') == 'dev') {
        echo json_encode($e->getMessage());
    }
    http_response_code($error_code);
}
