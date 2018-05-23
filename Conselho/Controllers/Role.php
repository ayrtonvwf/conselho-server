<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\Role\RoleMapper;

class Role extends Controller
{
    public function __construct()
    {
        parent::__construct(RoleMapper::class);
    }

    private function get_get_data() : array {
        $where = array_filter([
            'role_type_id = ?' => $this->input_int('role_type_id'),
            'user_id = ?' => $this->input_int('user_id')
        ]);
        if (!is_null($approved = $this->input_bool('approved'))) {
            $where['approved = ?'] = $approved;
        }
        return $where;
    }

    private function get_post_data() : array {
        return [
            'user_id' => $this->get_user()->id,
            'role_type_id' => $this->input_int('role_type_id')
        ];
    }

    private function get_patch_data() : array {
        return [
            'approved' => true,
            'updated_at' => date(self::DATETIME_INTERNAL_FORMAT)
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = self::DEFAULT_GET_RULES + [
            'approved'  => ['optional', 'integer', ['in', [0, 1]]],
            'role_type_id' => ['optional', 'integer', ['min', 1]],
            'user_id' => ['optional', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'role_type_id' => ['required', 'integer', ['min', 1]]
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

    public function patch(int $id) : ?string {
        if (!$record = $this->fetch($id)) {
            http_response_code(404);
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

        if (!$this->atlas()->delete($record)) {
            http_response_code(500);
            return;
        }

        http_response_code(204);
    }

}