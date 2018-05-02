<?php
namespace Conselho\Controllers;
use Conselho\Controller;

class StudentGrade extends Controller
{
    private function get_filters() : array {
        $filters = [
            'id' => $this->input_int('id'),
            'grade_id' => $this->input_int('grade_id'),
            'student_id' => $this->input_int('student_id'),
            'number' => [],
            'start' => [],
            'updated_at' => []
        ];
        if ($min_number = $this->input('min_number')) {
            $filters['number']['gte'] = $min_number;
        }
        if ($max_number = $this->input('max_number')) {
            $filters['number']['lte'] = $max_number;
        }
        if ($min_start = $this->input_string('min_start')) {
            $filters['start']['gte'] = $min_start;
        }
        if ($max_start = $this->input_string('max_start')) {
            $filters['start']['lte'] = $max_start;
        }
        if ($min_updated_at = $this->input('min_updated_at')) {
            $filters['updated_at']['gte'] = $min_updated_at;
        }
        if ($max_updated_at = $this->input('max_updated_at')) {
            $filters['updated_at']['lte'] = $max_updated_at;
        }
        return array_filter($filters);
    }

    private function get_data() : array {
        return     [
            'grade_id' => $this->input_int('grade_id'),
            'number' => (int) $this->input('number'),
            'student_id' => $this->input_int('student_id'),
            'start' => $this->input_string('start')
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = [
            'id' => ['optional', 'objectId', 'inCollection'],
            'grade_id' => ['optional', 'objectId', ['inCollection', 'grade']],
            'student_id' => ['optional', 'objectId', ['inCollection', 'student']],
            'min_number' => ['optional', 'integer', ['min', 1]],
            'max_number' => ['optional', 'integer', ['min', 1]],
            'max_start'  => ['optional', ['dateFormat', 'Y-m-d']],
            'min_start'  => ['optional', ['dateFormat', 'Y-m-d']],
            'max_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'min_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'page' => ['optional', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'grade_id' => ['required', 'objectId', ['inCollection', 'grade']],
            'number' => ['required', 'integer', ['min', 1]],
            'student_id' => ['required', 'objectId', ['inCollection', 'student']],
            'start' => ['required', ['dateFormat', 'Y-m-d']]
        ];

        return $this->run_validation($rules);
    }

    private function validate_put() : bool {
        $rules = [
            'id' => ['required', 'objectId', 'inCollection'],
            'grade_id' => ['optional', 'objectId', ['inCollection', 'grade']],
            'number' => ['optional', 'integer', ['min', 1]],
            'student_id' => ['optional', 'objectId', ['inCollection', 'student']],
            'start' => ['optional', ['dateFormat', 'Y-m-d']]
        ];

        return $this->run_validation($rules);
    }

    private function validate_delete() : bool {
        $rules = [
            'id' => ['required', 'objectId', 'inCollection']
        ];

        return $this->run_validation($rules);
    }

    // METHODS

    public function get() {
        if (!$this->validate_get()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }

        $filters = $this->get_filters();
        $pagination = $this->get_pagination();
        $default_model = $this->get_default_model();
        $results = $default_model::find($filters, $pagination)->toArray();
        $results = $this->sanitize_output($results);
        $return = [
            'results' => $results,
            'all_results' => $default_model::count($filters),
            'per_page' => $pagination['limit']
        ];
        return json_encode($return, $this->prettify());
    }

    public function post() {
        if (!$this->validate_post()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }

        $data = $this->get_data();
        $default_model = $this->get_default_model();

        $entity = new $default_model($data);
        if (!$entity->save()) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_INSERT'], $this->prettify());
        }
    }

    public function put() {
        if (!$this->validate_put()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }

        $default_model = $this->get_default_model();
        $criteria = ['id' => $this->input_int('id')];
        $entity = $default_model::one($criteria);

        $data = $this->get_data();

        if (!$entity->update($data)) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_UPDATE'], $this->prettify());
        }
    }

    public function delete() {
        if (!$this->validate_delete()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }

        $default_model = $this->get_default_model();
        $criteria = ['id' => $this->input_int('id')];
        $entity = $default_model::one($criteria);
        $entity->delete();
    }
}