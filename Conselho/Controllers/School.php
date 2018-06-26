<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\School\SchoolMapper;

class School extends Controller
{
    public function __construct()
    {
        parent::__construct(SchoolMapper::class);
    }

    private function get_get_data() : array {
        return array_filter([
            'name LIKE ?' => $this->input_search('search')
        ]);
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = self::DEFAULT_GET_RULES + [
            'search'  => ['optional', ['lengthBetween', 3, 50]]
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

}