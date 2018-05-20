<?php
namespace Conselho\DataSource\Role;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\RoleType\RoleTypeMapper;
use Conselho\DataSource\User\UserMapper;

/**
 * @inheritdoc
 */
class RoleMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('role_type', RoleTypeMapper::CLASS);
        $this->manyToOne('user', UserMapper::CLASS);
    }
}
