<?php
use Conselho\Controller;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class Council extends Controller
{
    public function __construct() {
        parent::__construct('council');
    }

    public function get() {
        $collection = $this->get_collection();
        $filters = $this->get_filters();
        $results = $collection->find($filters)->toArray();
        return json_encode(['results' => $results], $this->prettify());
    }

    private function get_filters() : array {
        $filters = [
            'start_date' => [],
            'end_date' => [],
            'updated_at' => [],
            'school_id' => $this->input('school_id') ? new ObjectId($this->input('school_id')) : null,
            'search' => $this->input('search')
        ];
        if ($this->input('min_start_date')) {
            $filters['start_date']['gte'] = new UTCDateTime($this->input('min_start_date'));
        }
        if ($this->input('max_start_date')) {
            $filters['start_date']['lte'] = new UTCDateTime($this->input('max_start_date'));
        }
        if ($this->input('min_end_date')) {
            $filters['end_date']['gte'] = new UTCDateTime($this->input('min_end_date'));
        }
        if ($this->input('max_end_date')) {
            $filters['end_date']['lte'] = new UTCDateTime($this->input('max_end_date'));
        }
        if ($this->input('min_updated_at')) {
            $filters['updated_at']['gte'] = new UTCDateTime($this->input('min_updated_at'));
        }
        if ($this->input('max_updated_at')) {
            $filters['updated_at']['lte'] = new UTCDateTime($this->input('max_updated_at'));
        }
        return array_filter($filters);
    }
    
    public function post() {
        if (!$this->validate_post()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }

        $data = [
            'start_date' => new UTCDateTime($this->input('start_date')),
            'end_date' => new UTCDateTime($this->input('end_date')),
            'name' => $this->input('name'),
            'school_id' => new ObjectId($this->input('school_id')),
            'updated_at' => new UTCDateTime()
        ];
        
        try {
            $this->get_collection()->insertOne($data);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_INSERT_COUNCIL'], $this->prettify());
        }
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

    public function put() {
        if (!$this->validate_put()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }

        $data = array_filter([
            'start_date' => $this->input('start_date') ? new UTCDateTime($this->input('start_date')) : null,
            'end_date' => $this->input('end_date') ? new UTCDateTime($this->input('end_date')) : null,
            'name' => $this->input('name'),
            'school_id' => $this->input('school_id') ? new ObjectId($this->input('school_id')) : null,
            'updated_at' => new UTCDateTime()
        ]);

        $criteria = ['_id' => new ObjectId($this->input('id'))];
        if (!$this->get_collection()->updateOne($criteria, ['$set' => $data])) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_UPDATE_COUNCIL'], $this->prettify());
        }
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

    public function delete() {
        if (!$this->validate_delete()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }
        
        $this->get_collection()->deleteOne(['_id' => $this->input('id')]);
    }

    private function validate_delete() : bool {
        $rules = [
            'id' => ['required', 'objectId', 'inCollection']
        ];

        return $this->run_validation($rules);
    }
}