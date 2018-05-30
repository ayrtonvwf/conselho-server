<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\Grade\GradeMapper;
use Conselho\DataSource\Subject\SubjectMapper;
use Conselho\DataSource\User\UserMapper;

class TeacherRequest extends Controller
{
    public function __construct()
    {
        parent::__construct(TeacherRequest::class);
    }

    private function get_get_data() : array {
        return array_filter([
            'grade_id = ?' => $this->input_int('grade_id'),
            'subject_id = ?' => $this->input_int('subject_id'),
            'user_id = ?' => $this->input_int('user_id')
        ]);
    }

    private function get_post_data() : array {
        return [
            'grade_id' => $this->input_int('grade_id'),
            'subject_id' => $this->input_int('subject_id'),
            'user_id' => $this->get_user()->id
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = self::DEFAULT_GET_RULES + [
            'grade_id' => ['optional', 'integer', ['min', 1], ['id_exists', GradeMapper::class]],
            'subject_id' => ['optional', 'integer', ['min', 1], ['id_exists', SubjectMapper::class]],
            'user_id' => ['optional', 'integer', ['min', 1], ['id_exists', UserMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'grade_id' => ['required', 'integer', ['min', 1], ['id_exists', GradeMapper::class]],
            'subject_id' => ['required', 'integer', ['min', 1], ['id_exists', SubjectMapper::class]]
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

    public function delete(int $id) : void {
        if (!$record = $this->fetch($id)) {
            http_response_code(404);
            return;
        }

        if (!$this->check_permission($id)) {
            http_response_code(403);
            return null;
        }

        if (!$this->delete_with_dependencies($record)) {
            http_response_code(409);
            return;
        }

        http_response_code(204);
    }
}