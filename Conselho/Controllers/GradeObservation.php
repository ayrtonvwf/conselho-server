<?php
namespace Conselho\Controllers;
use Conselho\Controller;

class GradeObservation extends Controller
{
    private function get_filters() : array {
        $filters = [
            '_id' => $this->input_id('id'),
            'updated_at' => [],
            'council_id' => $this->input_id('council_id'),
            'user_id' => $this->input_id('user_id'),
            'grade_id' => $this->input_id('grade_id'),
            'subject_id' => $this->input_id('subject_id')
        ];
        if ($this->input('search')) {
            $filters['$text'] = [
                'search' => $this->input('search'),
                'language' => 'pt'
            ];
        }
        if ($min_updated_at = $this->input_date('min_updated_at')) {
            $filters['updated_at']['gte'] = $min_updated_at;
        }
        if ($max_updated_at = $this->input_date('max_updated_at')) {
            $filters['updated_at']['lte'] = $max_updated_at;
        }
        return array_filter($filters);
    }

    private function get_data() : array {
        return [
            'council_id' => $this->input_id('council_id'),
            'user_id' => $this->input_id('user_id'),
            'grade_id' => $this->input_id('grade_id'),
            'subject_id' => $this->input_id('subject_id'),
            'description' => $this->input('description')
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = [
            'id' => ['optional', 'objectId', 'inCollection'],
            'council_id' => ['optional', 'objectId', ['inCollection', 'council']],
            'user_id' => ['optional', 'objectId', ['inCollection', 'user']],
            'grade_id' => ['optional', 'objectId', ['inCollection', 'grade']],
            'subject_id' => ['optional', 'objectId', ['inCollection', 'subject']],
            'max_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'min_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'search'  => ['optional', ['lengthMin', 3]],
            'page' => ['optional', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'council_id' => ['required', 'objectId', ['inCollection', 'council']],
            'user_id' => ['required', 'objectId', ['inCollection', 'user']],
            'grade_id' => ['required', 'objectId', ['inCollection', 'grade']],
            'subject_id' => ['required', 'objectId', ['inCollection', 'subject']],
            'description' => ['required', 'string', ['maxLength', 300]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_put() : bool {
        $rules = [
            'id' => ['required', 'objectId', 'inCollection'],
            'council_id' => ['optional', 'objectId', ['inCollection', 'council']],
            'user_id' => ['optional', 'objectId', ['inCollection', 'user']],
            'grade_id' => ['optional', 'objectId', ['inCollection', 'grade']],
            'subject_id' => ['optional', 'objectId', ['inCollection', 'subject']],
            'description' => ['optional', 'string', ['maxLength', 300]]
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
        $criteria = ['_id' => $this->input_id('id')];
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
        $criteria = ['_id' => $this->input_id('id')];
        $entity = $default_model::one($criteria);
        $entity->delete();
    }
}