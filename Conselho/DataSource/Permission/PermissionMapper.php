<?php
namespace Conselho\DataSource\Permission;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\RoleType\RoleTypeMapper;

/**
 * @inheritdoc
 */
class PermissionMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->oneToMany('role_types', RoleTypeMapper::CLASS)->on(['id' => 'permission_id']);
    }
}
