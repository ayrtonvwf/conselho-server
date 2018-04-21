<?php
namespace Conselho\Controllers;
use MiladRahimi\PHPRouter\Request;
use Conselho\Controller;
use MongoDb\BSON\UTCDateTime;
use MongoDb\BSON\ObjectId;

class Dev extends Controller
{

    public function playground() {
        die('it works');
    }

    public function rebuild_schemas() {
        $db = $this->get_db();
        $db->drop();

        $collection_names = [
            'council',
            'evaluation',
            'grade_observation',
            'grade_subject',
            'grade',
            'role_type',
            'role',
            'school',
            'student_grade',
            'student_medical_report',
            'student_observation',
            'student',
            'subject',
            'topic_type',
            'topic',
            'user_token',
            'user'
        ];

        array_walk($collection_names, function ($collection_name) use ($db) {
            $schema = json_decode(file_get_contents(BASE_PATH."/schema/$collection_name.json"));
            try {
                $db->createCollection($collection_name, ['validator' => ['$jsonSchema' => $schema]]);
            } catch (\Exception $error) {
                echo "Cannot create collection $collection_name:\n\n";
                print_r($error);
                exit;
            }
        });
    }

    public function populate_database() {
        $db = $this->get_db();
        $now = new UTCDateTime();

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
    }
}