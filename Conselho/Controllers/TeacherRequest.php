<?php
namespace Conselho\Controllers;
use Conselho\Controller;

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
            'grade_id' => ['optional', 'integer', ['min', 1]],
            'subject_id' => ['optional', 'integer', ['min', 1]],
            'user_id' => ['optional', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'grade_id' => ['required', 'integer', ['min', 1]],
            'subject_id' => ['required', 'integer', ['min', 1]],
        ];

        return $this->run_validation($rules);
    }

    // METHODS

    public function get() : string {
        if (!$this->validate_get()) {
            http_response_code(400);
            return $this->input_error_output();
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

        $atlas = $this->atlas();

        $blocking_dependencies = ['evaluations', 'student_observations', 'grade_observations'];
        $record = $atlas->fetchRecord($this->mapper_class_name, $record->id, $blocking_dependencies);
        $has_blocking_dependency = array_filter($blocking_dependencies, function($dependency) use ($record) {
            return (bool) $record->$dependency;
        });
        if ($has_blocking_dependency) {
            http_response_code(409);
            return;
        }

        $full_dependencies = array_merge($blocking_dependencies, ['council_topics', 'council_grades']);
        $record = $atlas->fetchRecord($this->mapper_class_name, $record->id, $full_dependencies);
        $transaction = $atlas->newTransaction();
        foreach ($full_dependencies as $dependency_name) {
            foreach ($record->$dependency_name as $dependency) {
                $transaction->delete($dependency);
            }
        }
        $transaction->delete($record);
        if (!$transaction->exec()) {
            http_response_code(500);
            return;
        }

        http_response_code(204);
    }
}