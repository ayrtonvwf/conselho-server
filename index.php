<?php

require __DIR__.'/vendor/autoload.php';

const BASE_PATH = __DIR__;

use Dotenv\Dotenv;
use MiladRahimi\PHPRouter\Router;
use MiladRahimi\PHPRouter\Exceptions\HttpError;

date_default_timezone_set('UTC');

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
    $router->get('/dev/rebuild_schemas[/]*', function() {
        $db_client = new MongoDB\Client;
        $db_name = getenv('DB_NAME');
        $db = $db_client->$db_name;
        $db->drop();

        $collection_names = [
            'council' => ['name'],
            'evaluation' => [],
            'grade_observation' => ['description'],
            'grade_subject' => [],
            'grade' => ['name'],
            'role_type' => ['name'],
            'role' => [],
            'school' => ['name'],
            'student_grade' => [],
            'student_medical_report' => ['description'],
            'student_observation' => ['description'],
            'student' => ['name'],
            'subject' => ['name'],
            'topic_type' => ['name'],
            'topic' => ['name'],
            'user_token' => [],
            'user' => ['name', 'email']
        ];

        array_walk($collection_names, function ($text_indexes, $collection_name) use ($db) {
            $schema = json_decode(file_get_contents(BASE_PATH."/schema/$collection_name.json"));
            try {
                $db->createCollection($collection_name, ['validator' => ['$jsonSchema' => $schema]]);
            } catch (\Exception $error) {
                echo "Cannot create collection $collection_name:\n\n";
                print_r($error);
                exit;
            }
            try {
                $text_indexes = array_map(function($field_name) {
                    return [$field_name => 'text'];
                }, $text_indexes);
                $db->$collection_name->createIndex($text_indexes);
            } catch (\Exception $error) {
                echo "Cannot create indexes of $collection_name";
                print_r($error);
                exit;
            }
        });
    });
    $router->get('/dev/populate_database[/]*', function() {
        $db_client = new MongoDB\Client;
        $db_name = getenv('DB_NAME');
        $db = $db_client->$db_name;
        $now = new \MongoDB\BSON\UTCDateTime();

        $school_id = $db->school->insertOne([
            'name' => 'Mondrone',
            'updated_at' => $now
        ])->getInsertedId();

        $user_id = $db->user->insertOne([
            'name' => 'Ayrton',
            'email' => 'ayrton.vargas33@gmail.com',
            'password' => password_hash('senha', PASSWORD_DEFAULT),
            'dev' => false,
            'updated_at' => $now
        ])->getInsertedId();

        $db->user_token->insertOne([
            'value' => 'pretend_its_a_token_value',
            'user_id' => $user_id,
            'expires_at' => $now,
            'updated_at' => $now
        ]);

        $topic_type_id = $db->topic_type->insertOne([
            'name' => '1 a 10',
            'reference' => '1-10',
            'updated_at' => $now
        ])->getInsertedId();

        $topic_id = $db->topic->insertOne([
            'name' => 'Conversa',
            'school_id' => $school_id,
            'topic_type_id' => $topic_type_id,
            'updated_at' => $now
        ])->getInsertedId();

        $council_id = $db->council->insertOne([
            'start_date' => $now,
            'end_date' => $now,
            'name' => '2018 - 1',
            'school_id' => $school_id,
            'updated_at' => $now
        ])->getInsertedId();

        $grade_id = $db->grade->insertOne([
            'name' => '1° A',
            'level' => 1,
            'school_id' => $school_id,
            'updated_at' => $now
        ])->getInsertedId();

        $subject_id = $db->subject->insertOne([
            'name' => 'Matemática',
            'school_id' => $school_id,
            'updated_at' => $now
        ])->getInsertedId();

        $role_type_id = $db->role_type->insertOne([
            'name' => 'Professor',
            'reference' => 'teacher',
            'updated_at' => $now
        ])->getInsertedId();

        $role = $db->role->insertOne([
            'user_id' => $user_id,
            'role_type_id' => $role_type_id,
            'school_id' => $school_id,
            'aproved' => true,
            'updated_at' => $now
        ])->getInsertedId();

        $student_id = $db->student->insertOne([
            'name' => 'Ayrton Vargas Witcel Fidelis',
            'school_id' => $school_id,
            'updated_at' => $now
        ])->getInsertedId();

        $db->student_grade->insertOne([
            'grade_id' => $grade_id,
            'number' => 27,
            'student_id' => $student_id,
            'start' => $now,
            'updated_at' => $now
        ]);

        $db->grade_subject->insertOne([
            'grade_id' => $grade_id,
            'subject_id' => $subject_id,
            'user_id' => $user_id,
            'aproved' => true,
            'updated_at' => $now
        ]);

        $db->grade_observation->insertOne([
            'council_id' => $council_id,
            'user_id' => $user_id,
            'grade_id' => $grade_id,
            'subject_id' => $subject_id,
            'description' => 'Turma muito conversadeira',
            'updated_at' => $now
        ]);

        $db->student_observation->insertOne([
            'user_id' => $user_id,
            'student_id' => $student_id,
            'grade_id' => $grade_id,
            'subject_id' => $subject_id,
            'council_id' => $council_id,
            'description' => 'Aluno desanimado',
            'updated_at' => $now
        ]);

        $db->student_medical_report->insertOne([
            'student_id' => $student_id,
            'subject_ids' => [$subject_id],
            'description' => 'Dislexia',
            'updated_at' => $now
        ]);

        $db->evaluation->insertOne([
            'user_id' => $user_id,
            'student_id' => $student_id,
            'grade_id' => $grade_id,
            'subject_id' => $subject_id,
            'council_id' => $council_id,
            'topic_id' => $topic_id,
            'value' => 9,
            'updated_at' => $now
        ]);
    });
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
    if (getenv('env') == 'dev') {
        print_r($e);
    }
}