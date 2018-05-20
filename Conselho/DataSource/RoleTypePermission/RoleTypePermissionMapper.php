<?php
namespace Conselho\DataSource\RoleTypePermission;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\Permission\PermissionMapper;
use Conselho\DataSource\RoleType\RoleTypeMapper;

/**
 * @inheritdoc
 */
class RoleTypePermissionMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('role_type', RoleTypeMapper::CLASS);
        $this->manyToOne('permission', PermissionMapper::CLASS);
    }
}
