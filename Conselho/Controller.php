<?php
namespace Conselho;
use Atlas\Orm\Atlas;
use Atlas\Orm\AtlasContainer;
use Atlas\Orm\Mapper\Record;
use Atlas\Orm\Mapper\RecordInterface;
use Conselho\DataSource\UserToken\UserTokenMapper;
use Valitron\Validator;
use PDO, PDOStatement, DateTime, DateTimeZone;

abstract class Controller {
    private $input_data = [];
    private $validation_errors = [];
    private $atlas;
    private $timezone = '+00:00';
    protected const DATETIME_EXTERNAL_FORMAT = 'Y-m-d\TH:i:sP';
    protected const DATETIME_INTERNAL_FORMAT = 'Y-m-d\TH:i:s';
    protected const DATE_FORMAT = 'Y-m-d';
    protected $mapper_class_name;
    private const ATLAS_MAPPERS = [
        DataSource\Council\CouncilMapper::CLASS,
        DataSource\CouncilGrade\CouncilGradeMapper::CLASS,
        DataSource\CouncilTopic\CouncilTopicMapper::CLASS,
        DataSource\Evaluation\EvaluationMapper::CLASS,
        DataSource\Grade\GradeMapper::CLASS,
        DataSource\GradeObservation\GradeObservationMapper::CLASS,
        DataSource\GradeSubject\GradeSubjectMapper::CLASS,
        DataSource\MedicalReport\MedicalReportMapper::CLASS,
        DataSource\MedicalReportSubject\MedicalReportSubjectMapper::CLASS,
        DataSource\Permission\PermissionMapper::CLASS,
        DataSource\Role\RoleMapper::CLASS,
        DataSource\RoleType\RoleTypeMapper::CLASS,
        DataSource\RoleTypePermission\RoleTypePermissionMapper::CLASS,
        DataSource\School\SchoolMapper::CLASS,
        DataSource\Student\StudentMapper::CLASS,
        DataSource\StudentGrade\StudentGradeMapper::CLASS,
        DataSource\StudentObservation\StudentObservationMapper::CLASS,
        DataSource\Subject\SubjectMapper::CLASS,
        DataSource\Teacher\TeacherMapper::CLASS,
        DataSource\TeacherRequest\TeacherRequestMapper::CLASS,
        DataSource\Topic\TopicMapper::CLASS,
        DataSource\TopicOption\TopicOptionMapper::CLASS,
        DataSource\User\UserMapper::CLASS,
        DataSource\UserToken\UserTokenMapper::CLASS
    ];

    public function __construct(string $mapper_class_name)
    {
        $this->mapper_class_name = $mapper_class_name;
        $this->input_data = $this->get_input_data();
        $timezone = ($_SERVER['HTTP_TIMEZONE'] ?? '+00:00');
        if (preg_match('#^[+-]\d\d:\d\d$#', $timezone)) {
            $this->timezone = $timezone;
        }
    }

    public function insert(array $data) : ?RecordInterface {
        $atlas = $this->atlas();
        $record = $atlas->newRecord($this->mapper_class_name, $data);
        return $atlas->insert($record) ? $record : null;
    }

    public function fetch(int $id) : ?RecordInterface {
        $atlas = $this->atlas();
        return $atlas->fetchRecord($this->mapper_class_name, $id);
    }

    public function output_datetime(string $date) : string {
        $date = new DateTime($date);
        $timezone = new DateTimeZone($this->timezone);
        $date->setTimezone($timezone);
        return $date->format(self::DATETIME_EXTERNAL_FORMAT);
    }

    private function get_input_data() : array {
        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PATCH'])) {
            return $_GET;
        }

        if (!$_SERVER['CONTENT_LENGTH']) {
            return [];
        }

        if (($_SERVER['CONTENT_TYPE'] ?? '') !== 'application/json') {
            http_response_code(415); // Unsupported Media Type
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (json_last_error()) {
            http_response_code(422); // Unprocessable Entity
            exit;
        }

        return $data ?? [];
    }

    protected function atlas() : Atlas {
        if (!$this->atlas) {
            $db_host = getenv('DB_HOST');
            $db_name = getenv('DB_NAME');
            $db_dns = "mysql:host=$db_host;dbname=$db_name;charset=UTF8";
            $db_user = getenv('DB_USER');
            $db_pass = getenv('DB_PASS');
            $pdo = new PDO($db_dns, $db_user, $db_pass);
            $pdo->query('SET time_zone =  \'+00:00\'');
            $atlas_container = new AtlasContainer($pdo);
            $atlas_container->setMappers(self::ATLAS_MAPPERS);
            $this->atlas = $atlas_container->getAtlas();
        }
        return $this->atlas;
    }

    protected function get_pagination(int $limit = 50) : array {
        $page = $this->input_int('page');
        if (!$page) {
            $page = 1;
        }
        $offset = $limit*($page-1);
        return [
            'page' => $page,
            'limit' => $limit,
            'offset' => $offset
        ];
    }

    protected function pretty() : ?int {
        return !empty($_SERVER['HTTP_PRETTY_OUTPUT']) ? JSON_PRETTY_PRINT : null;
    }

    protected function input_datetime(string $key) : ?string {
        if (is_null($datetime = $this->input_raw($key))) {
            return null;
        }
        $datetime = new DateTime($datetime);
        $timezone = new DateTimeZone($this->timezone);
        $datetime->setTimezone($timezone);
        return $datetime->format(self::DATETIME_INTERNAL_FORMAT);
    }

    protected function input_raw(string $key) {
        return $this->input_data[$key] ?? null;
    }

    protected function input_string(string $key) : ?string {
        $value = $this->input_raw($key);

        return !is_null($value) ? trim(strip_tags($value)) : null;
    }

    protected function input_int(string $key) : ?int {
        $value = $this->input_raw($key);
        return !is_null($value) ? (int) $value : null;
    }

    protected function input_bool(string $key) : ?bool {
        $value = $this->input_raw($key);
        return !is_null($value) ? (bool) $value : null;
    }

    protected function get_user() : ?Record {
        $atlas = $this->atlas();
        $user_token = $atlas->fetchRecordBy(UserTokenMapper::CLASS, ['value' => $this->get_token()], ['user']);
        return $user_token->user;
    }

    protected function get_token() : ?string {
        return $_SERVER['HTTP_TOKEN'] ?? null;
    }

    protected function run_validation(array $rules) : bool {
        $data = [];
        foreach ($this->input_data as $key => $value) {
            $data[$key] = is_string($value) ? trim(strip_tags($value)) : $value;
        }

        $validator = new Validator($data);
        $validator->mapFieldsRules($rules);
        $validator->validate();
        $this->validation_errors = $validator->errors();

        return !$this->validation_errors;
    }

    protected function get_validation_errors() : array {
        return $this->validation_errors;
    }

    protected function bind_values(PDOStatement $statement, array $values) : PDOStatement {
        foreach ($values as $parameter => $value) {
            $parameter_type = $this->get_pdo_parameter_type($value);
            $statement->bindValue(":$parameter", $value, $parameter_type);
        }
        return $statement;
    }

    private function get_pdo_parameter_type($value) : ?int {
        if (is_int($value)) {
            return PDO::PARAM_INT;
        }
        if (is_bool($value)) {
            return PDO::PARAM_BOOL;
        }
        if (is_string($value)) {
            return PDO::PARAM_STR;
        }
        if (is_null($value)) {
            return PDO::PARAM_NULL;
        }
        return null;
    }
}