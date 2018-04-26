<?php
namespace Conselho\Controllers;
use Conselho\Controller;

class Evaluation extends Controller
{
    private function get_filters() : array {
        $filters = [
            '_id' => $this->input_id('id'),
            'value' => [],
            'updated_at' => [],
            'user_id' => $this->input_id('user_id'),
            'student_id' => $this->input_id('student_id'),
            'grade_id' => $this->input_id('grade_id'),
            'subject_id' => $this->input_id('subject_id'),
            'council_id' => $this->input_id('council_id'),
            'topic_id' => $this->input_id('topic_id')
        ];
        if ($this->input('min_value')) {
            $filters['value']['gte'] = $this->input('min_value');
        }
        if ($this->input('max_value')) {
            $filters['value']['lte'] = $this->input('max_value');
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
            'user_id' => $this->input_id('user_id'),
            'student_id' => $this->input_id('student_id'),
            'grade_id' => $this->input_id('grade_id'),
            'subject_id' => $this->input_id('subject_id'),
            'council_id' => $this->input_id('council_id'),
            'topic_id' => $this->input_id('topic_id'),
            'value' => $this->input('value')
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = [
            'id' => ['optional', 'objectId', 'inCollection'],
            'user_id' => ['optional', 'objectId', ['inCollection', 'user']],
            'student_id' => ['optional', 'objectId', ['inCollection', 'student']],
            'grade_id' => ['optional', 'objectId', ['inCollection', 'grade']],
            'subject_id' => ['optional', 'objectId', ['inCollection', 'subject_id']],
            'council_id' => ['optional', 'objectId', ['inCollection', 'council_id']],
            'topic_id' => ['optional', 'objectId', ['inCollection', 'topic_id']],
            'min_value' => ['optional'],
            'max_value' => ['optional'],
            'max_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'min_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'page' => ['optional', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'user_id' => ['required', 'objectId', ['inCollection', 'user']],
            'student_id' => ['required', 'objectId', ['inCollection', 'student']],
            'grade_id' => ['required', 'objectId', ['inCollection', 'grade']],
            'subject_id' => ['required', 'objectId', ['inCollection', 'subject']],
            'council_id' => ['required', 'objectId', ['inCollection', 'council']],
            'topic_id' => ['required', 'objectId', ['inCollection', 'topic']],
            'value' => ['required']
        ];

        return $this->run_validation($rules);
    }

    private function validate_put() : bool {
        $rules = [
            'id' => ['required', 'objectId', 'inCollection'],
            'user_id' => ['optional', 'objectId', ['inCollection', 'user']],
            'student_id' => ['optional', 'objectId', ['inCollection', 'student']],
            'grade_id' => ['optional', 'objectId', ['inCollection', 'grade']],
            'subject_id' => ['optional', 'objectId', ['inCollection', 'subject']],
            'council_id' => ['optional', 'objectId', ['inCollection', 'council']],
            'topic_id' => ['optional', 'objectId', ['inCollection', 'topic']],
            'value' => ['optional']
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