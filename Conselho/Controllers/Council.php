<?php
namespace Conselho\Controllers;
use Conselho\Controller;

class Council extends Controller
{
    private function get_filters() : array {
        $filters = [
            'id' => $this->input_int('id'),
            'start_date' => [],
            'end_date' => [],
            'updated_at' => [],
            'school_id' => $this->input_int('school_id'),
            'search' => $this->input('search')
        ];
        if ($min_start_date = $this->input_string('min_start_date')) {
            $filters['start_date']['gte'] = $min_start_date;
        }
        if ($max_start_date = $this->input_string('max_start_date')) {
            $filters['start_date']['lte'] = $max_start_date;
        }
        if ($min_end_date = $this->input_string('min_end_date')) {
            $filters['end_date']['gte'] = $min_end_date;
        }
        if ($max_end_date = $this->input_string('max_end_date')) {
            $filters['end_date']['lte'] = $max_end_date;
        }
        if ($min_updated_at = $this->input_string('min_updated_at')) {
            $filters['updated_at']['gte'] = $min_updated_at;
        }
        if ($max_updated_at = $this->input_string('max_updated_at')) {
            $filters['updated_at']['lte'] = $max_updated_at;
        }
        return array_filter($filters);
    }

    private function get_data() : array {
        return [
            'start_date' => $this->input_string('start_date'),
            'end_date' => $this->input_string('end_date'),
            'name' => $this->input('name'),
            'school_id' => $this->input_int('school_id')
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = [
            'id' => ['optional', 'objectId', 'inCollection'],
            'school_id' => ['optional', 'objectId', ['inCollection', 'school']],
            'max_start_date'  => ['optional', ['dateFormat', 'Y-m-d']],
            'min_start_date'  => ['optional', ['dateFormat', 'Y-m-d']],
            'max_end_date'  => ['optional', ['dateFormat', 'Y-m-d']],
            'min_end_date'  => ['optional', ['dateFormat', 'Y-m-d']],
            'max_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'min_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'search'  => ['optional', ['lengthMin', 3]],
            'page' => ['optional', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'start_date'  => ['required', ['dateFormat', 'Y-m-d']],
            'end_date'  => ['required', ['dateFormat', 'Y-m-d']],
            'name'  => ['required', ['lengthBetween', 5, 30]],
            'school_id' => ['required', 'objectId', ['inCollection', 'school']]
        ];

        return $this->run_validation($rules);
    }

    private function validate_put() : bool {
        $rules = [
            'id' => ['required', 'objectId', 'inCollection'],
            'start_date'  => ['optional', ['dateFormat', 'Y-m-d']],
            'end_date'  => ['optional', ['dateFormat', 'Y-m-d']],
            'name'  => ['optional', ['lengthBetween', 5, 30]],
            'school_id' => ['optional', 'objectId', ['inCollection', 'school']]
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