<?php
namespace Conselho\DataSource\UserToken;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\User\UserMapper;

/**
 * @inheritdoc
 */
class UserTokenMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('user', UserMapper::CLASS);
    }
}
