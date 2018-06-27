<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\Grade\GradeMapper;
use Conselho\DataSource\Subject\SubjectMapper;
use Conselho\DataSource\Teacher\TeacherMapper;
use Conselho\DataSource\User\UserMapper;

class Teacher extends Controller
{
    public function __construct()
    {
        parent::__construct(TeacherMapper::class);
    }

    private function get_get_data() : array {
        return array_filter([
            'start_date >= ?' => $this->input_string('min_start_date'),
            'start_date <= ?' => $this->input_string('max_start_date'),
            'end_date >= ?' => $this->input_string('min_end_date'),
            'end_date <= ?' => $this->input_string('max_end_date'),
            'grade_id = ?' => $this->input_int('grade_id'),
            'subject_id = ?' => $this->input_int('subject_id'),
            'user_id = ?' => $this->input_int('user_id')
        ]);
    }

    private function get_post_data() : array {
        return [
            'start_date' => $this->input_string('start_date'),
            'end_date' => $this->input_string('end_date'),
            'grade_id' => $this->input_int('grade_id'),
            'subject_id' => $this->input_int('subject_id'),
            'user_id' => $this->input_int('user_id')
        ];
    }
    private function get_patch_data() : array {
        return array_filter([
            'start_date' => $this->input_string('start_date'),
            'end_date' => $this->input_string('end_date'),
            'grade_id' => $this->input_int('grade_id'),
            'subject_id' => $this->input_int('subject_id'),
            'user_id' => $this->input_int('user_id'),
            'updated_at' => date(self::DATETIME_INTERNAL_FORMAT)
        ]);
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = self::DEFAULT_GET_RULES + [
            'min_start_date'  => ['optional', ['dateFormat', self::DATE_FORMAT]],
            'max_start_date'  => ['optional', ['dateFormat', self::DATE_FORMAT]],
            'min_end_date'  => ['optional', ['dateFormat', self::DATE_FORMAT]],
            'max_end_date'  => ['optional', ['dateFormat', self::DATE_FORMAT]],
            'grade_id' => ['optional', 'integer', ['min', 1], ['id_exists', GradeMapper::class]],
            'subject_id' => ['optional', 'integer', ['min', 1], ['id_exists', SubjectMapper::class]],
            'user_id' => ['optional', 'integer', ['min', 1], ['id_exists', UserMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'start_date'  => ['required', ['dateFormat', self::DATE_FORMAT]],
            'end_date'  => ['required', ['dateFormat', self::DATE_FORMAT]],
            'grade_id' => ['required', 'integer', ['min', 1], ['id_exists', GradeMapper::class]],
            'subject_id' => ['required', 'integer', ['min', 1], ['id_exists', SubjectMapper::class]],
            'user_id' => ['required', 'integer', ['min', 1], ['id_exists', UserMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_patch() : bool {
        $rules = [
            'start_date'  => ['optional', ['dateFormat', self::DATE_FORMAT]],
            'end_date'  => ['optional', ['dateFormat', self::DATE_FORMAT]],
            'grade_id' => ['optional', 'integer', ['min', 1], ['id_exists', GradeMapper::class]],
            'subject_id' => ['optional', 'integer', ['min', 1], ['id_exists', SubjectMapper::class]],
            'user_id' => ['optional', 'integer', ['min', 1], ['id_exists', UserMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function has_conflict() : bool {
        $atlas = $this->atlas();
        $school_ids = [];

        if ($grade_id = $this->input_int('grade_id')) {
            $school_ids[] = $atlas->fetchRecord(GradeMapper::class, $grade_id)->school_id;
        }
        if ($subject_id = $this->input_int('subject_id')) {
            $school_ids[] = $atlas->fetchRecord(SubjectMapper::class, $subject_id)->school_id;
        }

        return count(array_unique($school_ids)) > 1;
    }

    private function check_permission(int $id = null) : bool {
        $atlas = $this->atlas();

        if ($id) {
            $school_id = $atlas->fetchRecord($this->mapper_class_name, $id, ['grade'])->grade->school_id;
        } else {
            $school_id = $atlas->fetchRecord(GradeMapper::class, $this->input_int('grade_id'))->school_id;
        }

        return $this->has_permission('evaluate', $school_id);
    }

    // METHODS

    public function get() : string {
        if (!$this->validate_get()) {
            http_response_code(400);
            return $this->input_error_output();
        }

        if ($this->has_conflict()) {
            http_response_code(409);
            return null;
        }

        $where = $this->get_get_data();

        $result = $this->search($where);

        return json_encode($result, $this->pretty());
    }

    public function post() : ?string {
        if (!$this->validate_post()) {
            http_response_code(400);
            return json_encode([
                'input_errors' => $this->get_validation_errors()
            ], $this->pretty());
        }

        if ($this->has_conflict()) {
            http_response_code(409);
            return null;
        }

        if (!$this->check_permission()) {
            http_response_code(403);
            return null;
        }

        $data = $this->get_post_data();
        if (!$record = $this->insert($data)) {
            http_response_code(500);
            return null;
        }

        return $this->post_output($record);
    }

    public function patch(int $id) : ?string {
        if (!$record = $this->fetch($id)) {
            http_response_code(404);
            return null;
        }

        if (!$this->check_permission($id)) {
            http_response_code(403);
            return null;
        }

        if (!$this->validate_patch()) {
            http_response_code(400);
            return json_encode([
                'input_errors' => $this->get_validation_errors()
            ], $this->pretty());
        }

        if ($this->has_conflict()) {
            http_response_code(409);
            return null;
        }

        $data = $this->get_patch_data();
        $record->set($data);
        if (!$this->atlas()->update($record)) {
            http_response_code(500);
            return null;
        }

        return $this->patch_output($record);
    }

    public function delete(int $id) : void {
        if (!$record = $this->fetch($id)) {
            http_response_code(404);
            return;
        }

        if (!$this->check_permission($id)) {
            http_response_code(403);
            return;
        }

        if (!$this->delete_with_dependencies($record)) {
            http_response_code(409);
            return;
        }

        http_response_code(204);
    }
}