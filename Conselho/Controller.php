<?php
namespace Conselho;
use Atlas\Orm\Atlas;
use Atlas\Orm\AtlasContainer;
use Atlas\Orm\Mapper\Record;
use Atlas\Orm\Mapper\RecordInterface;
use Conselho\DataSource\Role\RoleMapper;
use Conselho\DataSource\UserToken\UserTokenMapper;
use Valitron\Validator;
use PDO, DateTime, DateTimeZone, Exception;

abstract class Controller {
    private $input_data = [];
    private $validation_errors = [];
    private $atlas;
    private $timezone = '+00:00';
    protected const DATETIME_EXTERNAL_FORMAT = 'Y-m-d\TH:i:sP';
    protected const DATETIME_INTERNAL_FORMAT = 'Y-m-d\TH:i:s';
    protected const DATE_FORMAT = 'Y-m-d';
    protected $mapper_class_name;

    protected const DEFAULT_GET_RULES = [
        'id' => ['optional', 'integer', ['min', 1], ['id_exists']],
        'page' => ['optional', 'integer', ['min', 1]],
        'min_created_at'  => ['optional', ['dateFormat', self::DATETIME_EXTERNAL_FORMAT]],
        'max_created_at'  => ['optional', ['dateFormat', self::DATETIME_EXTERNAL_FORMAT]],
        'min_updated_at'  => ['optional', ['dateFormat', self::DATETIME_EXTERNAL_FORMAT]],
        'max_updated_at'  => ['optional', ['dateFormat', self::DATETIME_EXTERNAL_FORMAT]]
    ];
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

    // DB HELPERS

    private function default_get_filters() : array {
        return array_filter([
            'id = ?' => $this->input_int('id'),
            'created_at >= ?' => $this->input_datetime('min_created_at'),
            'created_at <= ?' => $this->input_datetime('max_created_at'),
            'updated_at >= ?' => $this->input_datetime('min_updated_at'),
            'updated_at <= ?' => $this->input_datetime('max_updated_at')
        ]);
    }

    public function search(array $where, array $cols = ['*']) : array{
        $where = $this->default_get_filters() + $where;

        $atlas = $this->atlas();
        $select = $atlas->select($this->mapper_class_name);
        foreach ($where as $condition => $value) {
            if (is_array($value)) {
                $select->where($condition);
                $select->bindValues($value);
            } else {
                $select->where($condition, $value);
            }
        }
        $pagination = $this->get_pagination();
        $select->limit($pagination['limit']);
        $select->offset($pagination['offset']);
        $select->cols($cols);

        $results = array_map(function($result) {
            $result['created_at'] = $this->output_datetime($result['created_at']);
            $result['updated_at'] = $this->output_datetime($result['updated_at']);
            return $result;
        }, $select->fetchAll());

        return [
            'total_results' => $select->fetchCount(),
            'current_page' => $pagination['page'],
            'max_results_per_page' => $pagination['limit'],
            'results' => $results
        ];
    }

    public function insert(array $data) : ?RecordInterface {
        $atlas = $this->atlas();
        $data['created_at'] = $data['updated_at'] = date(self::DATETIME_INTERNAL_FORMAT);
        $record = $atlas->newRecord($this->mapper_class_name, $data);
        return $atlas->insert($record) ? $record : null;
    }

    public function fetch(int $id) : ?RecordInterface {
        $atlas = $this->atlas();
        return $atlas->fetchRecord($this->mapper_class_name, $id);
    }

    public function delete_with_dependencies(RecordInterface $record, array $blocking_dependencies = []) : bool {
        $atlas = $this->atlas();
        $all_dependencies = array_keys($record->getRelated()->getFields());

        $record = $atlas->fetchRecord($this->mapper_class_name, $record->id, $all_dependencies);

        $has_blocking_dependency = array_filter($blocking_dependencies, function($dependency) use ($record) {
            return (bool) $record->$dependency;
        });

        if ($has_blocking_dependency) {
            return false;
        }

        $transaction = $atlas->newTransaction();
        foreach ($all_dependencies as $dependency_name) {
            foreach ($record->$dependency_name as $dependency) {
                $transaction->delete($dependency);
            }
        }
        $transaction->delete($record);

        try {
            return $transaction->exec();
        } catch (Exception $e) {
            return false;
        }
    }

    public function has_permission(string $reference, int $school_id) : bool {
        $atlas = $this->atlas();
        $user = $this->get_user();
        $roles = $atlas->fetchRecordsBy(RoleMapper::class, ['user_id' => $user->id, 'approved' => true], ['role_type' => ['role_type_permissions' => ['permission']]]);

        $roles = array_filter($roles, function ($role) use ($school_id, $reference) {
            if ($role->role_type->school_id != $school_id) {
                return false;
            }

            return array_filter($role->role_type->role_type_permissions->getArrayCopy(), function ($role_type_permission) use ($reference) {
                return $role_type_permission['permission']['reference'] == $reference;
            });
        }); // filters out the ones without any permission

        return (bool) $roles;
    }

    // OUTPUT HELPERS

    public function input_error_output() : string {
        $data = [
            'input_errors' => $this->get_validation_errors()
        ];
        return json_encode($data, $this->pretty());
    }

    public function post_output(RecordInterface $record) : string {
        $data = [
            'id' => (int) $record->id,
            'created_at' => $this->output_datetime($record->created_at)
        ];
        return json_encode($data, $this->pretty());
    }

    public function patch_output(RecordInterface $record) : string {
        $data = [
            'updated_at' => $this->output_datetime($record->updated_at)
        ];
        return json_encode($data, $this->pretty());
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

    // INPUT HELPERS

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

    protected function input_search(string $key) : ?string {
        $value = $this->input_string($key);
        return !is_null($value) ? "%$value%" : null;
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

        $atlas = $this->atlas();
        $validator = new Validator($data);
        $validator->addInstanceRule('id_exists', function(string $field, ?int $value, array $extra) use ($atlas) : bool {
            if (!$value) {
                return false;
            }
            return (bool) $atlas->fetchRecord($extra[0] ?? $this->mapper_class_name, $value);
        }, 'The {field} does not exists in db');
        $validator->addInstanceRule('is_bool', function(string $field, $value, array $extra) : bool {
            $allowed_values = [true, false, 1, 0, '1', '0'];
            return in_array($value, $allowed_values, true);
        }, 'The {field} must be a boolean');
        $validator->mapFieldsRules($rules);
        $validator->validate();
        $this->validation_errors = $validator->errors();

        return !$this->validation_errors;
    }

    protected function get_validation_errors() : array {
        return $this->validation_errors;
    }
}