<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\Permission\PermissionMapper;
use Conselho\DataSource\RoleType\RoleTypeMapper;
use Conselho\DataSource\RoleTypePermission\RoleTypePermissionMapper;

class RoleTypePermission extends Controller
{
    public function __construct()
    {
        parent::__construct(RoleTypePermissionMapper::class);
    }

    private function get_get_data() : array {
        return array_filter([
            'role_type_id = ?' => $this->input_int('role_type_id'),
            'permission_id = ?' => $this->input_int('permission_id')
        ]);
    }

    private function get_post_data() : array {
        return [
            'role_type_id' => $this->input_int('role_type_id'),
            'permission_id' => $this->input_int('permission_id')
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = self::DEFAULT_GET_RULES + [
            'role_type_id' => ['optional', 'integer', ['min', 1], ['id_exists', RoleTypeMapper::class]],
            'permission_id' => ['optional', 'integer', ['min', 1], ['id_exists', PermissionMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'role_type_id' => ['required', 'integer', ['min', 1], ['id_exists', RoleTypeMapper::class]],
            'permission_id' => ['required', 'integer', ['min', 1], ['id_exists', PermissionMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function check_permission(int $id = null) : bool {
        $atlas = $this->atlas();

        if ($id) {
            $school_id = $atlas->fetchRecord($this->mapper_class_name, $id, ['role_type'])->role_type->school_id;
        } else {
            $school_id = $atlas->fetchRecord(RoleTypeMapper::class, $this->input_int('role_type_id'))->school_id;
        }

        return $this->has_permission('role_type', $school_id);
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