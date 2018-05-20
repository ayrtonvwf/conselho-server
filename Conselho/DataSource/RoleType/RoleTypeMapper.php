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
        $this->manyToOne('school', SchoolMapper::CLASS);
        $this->oneToMany('roles', RoleMapper::CLASS);
        $this->oneToMany('role_type_permissions', RoleTypePermissionMapper::CLASS);
    }
}
