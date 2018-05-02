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
    die(json_encode(['error_code' => 'WRONG_HTTP_ACCEPT']));
}

if (($_SERVER['CONTENT_TYPE'] ?? '') != 'application/json') {
    http_response_code(400);
    die(json_encode(['error_code' => 'WRONG_CONTENT_TYPE']));
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

$router->map('GET', '/grade_observation[/]*', 'Conselho\\Controllers\\GradeObservation@get', 'Conselho\\Auth@check');
$router->map('POST', '/grade_observation[/]*', 'Conselho\\Controllers\\GradeObservation@post', 'Conselho\\Auth@check');
$router->map('PUT', '/grade_observation[/]*', 'Conselho\\Controllers\\GradeObservation@put', 'Conselho\\Auth@check');
$router->map('DELETE', '/grade_observation[/]*', 'Conselho\\Controllers\\GradeObservation@delete', 'Conselho\\Auth@check');

$router->map('GET', '/grade_subject[/]*', 'Conselho\\Controllers\\GradeSubject@get', 'Conselho\\Auth@check');
$router->map('POST', '/grade_subject[/]*', 'Conselho\\Controllers\\GradeSubject@post', 'Conselho\\Auth@check');
$router->map('PUT', '/grade_subject[/]*', 'Conselho\\Controllers\\GradeSubject@put', 'Conselho\\Auth@check');
$router->map('DELETE', '/grade_subject[/]*', 'Conselho\\Controllers\\GradeSubject@delete', 'Conselho\\Auth@check');

$router->map('GET', '/medical_report[/]*', 'Conselho\\Controllers\\MedicalReport@get', 'Conselho\\Auth@check');
$router->map('POST', '/medical_report[/]*', 'Conselho\\Controllers\\MedicalReport@post', 'Conselho\\Auth@check');
$router->map('PUT', '/medical_report[/]*', 'Conselho\\Controllers\\MedicalReport@put', 'Conselho\\Auth@check');
$router->map('DELETE', '/medical_report[/]*', 'Conselho\\Controllers\\MedicalReport@delete', 'Conselho\\Auth@check');

$router->map('GET', '/medical_report_subject[/]*', 'Conselho\\Controllers\\MedicalReportSubject@get', 'Conselho\\Auth@check');
$router->map('POST', '/medical_report_subject[/]*', 'Conselho\\Controllers\\MedicalReportSubject@post', 'Conselho\\Auth@check');
$router->map('PUT', '/medical_report_subject[/]*', 'Conselho\\Controllers\\MedicalReportSubject@put', 'Conselho\\Auth@check');
$router->map('DELETE', '/medical_report_subject[/]*', 'Conselho\\Controllers\\MedicalReportSubject@delete', 'Conselho\\Auth@check');

$router->map('GET', '/role[/]*', 'Conselho\\Controllers\\Role@get', 'Conselho\\Auth@check');
$router->map('POST', '/role[/]*', 'Conselho\\Controllers\\Role@post', 'Conselho\\Auth@check');
$router->map('PUT', '/role[/]*', 'Conselho\\Controllers\\Role@put', 'Conselho\\Auth@check');
$router->map('DELETE', '/role[/]*', 'Conselho\\Controllers\\Role@delete', 'Conselho\\Auth@check');

$router->map('GET', '/role_type[/]*', 'Conselho\\Controllers\\RoleType@get', 'Conselho\\Auth@check');

$router->map('GET', '/school[/]*', 'Conselho\\Controllers\\School@get', 'Conselho\\Auth@check');

$router->map('GET', '/student[/]*', 'Conselho\\Controllers\\Student@get', 'Conselho\\Auth@check');
$router->map('POST', '/student[/]*', 'Conselho\\Controllers\\Student@post', 'Conselho\\Auth@check');
$router->map('PUT', '/student[/]*', 'Conselho\\Controllers\\Student@put', 'Conselho\\Auth@check');
$router->map('DELETE', '/student[/]*', 'Conselho\\Controllers\\Student@delete', 'Conselho\\Auth@check');

$router->map('GET', '/student_grade[/]*', 'Conselho\\Controllers\\StudentGrade@get', 'Conselho\\Auth@check');
$router->map('POST', '/student_grade[/]*', 'Conselho\\Controllers\\StudentGrade@post', 'Conselho\\Auth@check');
$router->map('PUT', '/student_grade[/]*', 'Conselho\\Controllers\\StudentGrade@put', 'Conselho\\Auth@check');
$router->map('DELETE', '/student_grade[/]*', 'Conselho\\Controllers\\StudentGrade@delete', 'Conselho\\Auth@check');

$router->map('GET', '/student_observation[/]*', 'Conselho\\Controllers\\StudentObservation@get', 'Conselho\\Auth@check');
$router->map('POST', '/student_observation[/]*', 'Conselho\\Controllers\\StudentObservation@post', 'Conselho\\Auth@check');
$router->map('PUT', '/student_observation[/]*', 'Conselho\\Controllers\\StudentObservation@put', 'Conselho\\Auth@check');
$router->map('DELETE', '/student_observation[/]*', 'Conselho\\Controllers\\StudentObservation@delete', 'Conselho\\Auth@check');

$router->map('GET', '/subject[/]*', 'Conselho\\Controllers\\Subject@get', 'Conselho\\Auth@check');
$router->map('POST', '/subject[/]*', 'Conselho\\Controllers\\Subject@post', 'Conselho\\Auth@check');
$router->map('PUT', '/subject[/]*', 'Conselho\\Controllers\\Subject@put', 'Conselho\\Auth@check');
$router->map('DELETE', '/subject[/]*', 'Conselho\\Controllers\\Subject@delete', 'Conselho\\Auth@check');

$router->map('GET', '/topic[/]*', 'Conselho\\Controllers\\Topic@get', 'Conselho\\Auth@check');
$router->map('POST', '/topic[/]*', 'Conselho\\Controllers\\Topic@post', 'Conselho\\Auth@check');
$router->map('PUT', '/topic[/]*', 'Conselho\\Controllers\\Topic@put', 'Conselho\\Auth@check');
$router->map('DELETE', '/topic[/]*', 'Conselho\\Controllers\\Topic@delete', 'Conselho\\Auth@check');

$router->map('GET', '/topic_type[/]*', 'Conselho\\Controllers\\TopicType@get', 'Conselho\\Auth@check');

$router->map('GET', '/user[/]*', 'Conselho\\Controllers\\User@get', 'Conselho\\Auth@check');
$router->map('POST', '/user[/]*', 'Conselho\\Controllers\\User@post');
$router->map('PUT', '/user[/]*', 'Conselho\\Controllers\\User@put', 'Conselho\\Auth@check');
$router->map('DELETE', '/user[/]*', 'Conselho\\Controllers\\User@delete', 'Conselho\\Auth@check');

$router->map('POST', '/user_token[/]*', 'Conselho\\Controllers\\UserToken@post');

try {
    $router->dispatch();
} catch(HttpError $error) {
    $error_code = $error->getMessage() == '404' ? 'NOT_FOUND' : 'UNKNOWN_ERROR';
    die(json_encode(['error_code' => $error_code]));
} catch(Exception $e) {
    die(json_encode(['error_code' => 'INTERNAL_ERROR']));
}