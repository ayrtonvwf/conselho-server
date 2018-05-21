<?php
namespace Conselho\DataSource\RoleType;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\Role\RoleMapper;
use Conselho\DataSource\RoleTypePermission\RoleTypePermissionMapper;
use Conselho\DataSource\School\SchoolMapper;

/**
 * @inheritdoc
 */
class RoleTypeMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('school', SchoolMapper::CLASS)->on(['school_id' => 'id']);
        $this->oneToMany('roles', RoleMapper::CLASS)->on(['id' => 'role_type_id']);
        $this->oneToMany('role_type_permissions', RoleTypePermissionMapper::CLASS)->on(['id' => 'role_type_permission_id']);
    }
}
