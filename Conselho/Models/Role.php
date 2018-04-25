<?php
namespace Conselho\Models;
use Purekid\Mongodm\Model;

class Role extends Model
{
    static $collection = 'role';

    protected static $attrs = [
        'user_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\User'],
        'role_type_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\RoleType'],
        'school_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\School'],
        'aproved' => ['type' => Model::DATA_TYPE_BOOLEAN],
        'updated_at' => ['type' => Model::DATA_TYPE_DATE]
    ];

    protected function __preSave() {
        $this->updated_at = new DateTime();
    }
}