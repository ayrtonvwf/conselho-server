<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\Permission\PermissionMapper;

class Permission extends Controller
{
    public function __construct()
    {
        parent::__construct(PermissionMapper::class);
    }

    private function get_get_data() : array
    {
        $search_values = array_filter([
            'name_search' => $search = $this->input_search('search'),
            'reference_search' => $search
        ]);
        return array_filter([
            'reference = ?' => $this->input_string('reference'),
            '(name LIKE :name_search OR reference LIKE :reference_search)' => $search_values
        ]);
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = self::DEFAULT_GET_RULES + [
            'search'  => ['optional', ['lengthMin', 3]],
            'reference'  => ['optional', ['lengthMin', 3]]
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