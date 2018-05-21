<?php
/**
 * This file was generated by Atlas. Changes will be overwritten.
 */
namespace Conselho\DataSource\UserToken;

use Atlas\Orm\Table\AbstractTable;

/**
 * @inheritdoc
 */
class UserTokenTable extends AbstractTable
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'user_token';
    }

    /**
     * @inheritdoc
     */
    public function getColNames()
    {
        return [
            'id',
            'value',
            'expires_at',
            'user_id',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCols()
    {
        return [
            'id' => (object) [
                'name' => 'id',
                'type' => 'int',
                'size' => 10,
                'scale' => null,
                'notnull' => true,
                'default' => null,
                'autoinc' => true,
                'primary' => true,
            ],
            'value' => (object) [
                'name' => 'value',
                'type' => 'varchar',
                'size' => 255,
                'scale' => null,
                'notnull' => true,
                'default' => null,
                'autoinc' => false,
                'primary' => false,
            ],
            'expires_at' => (object) [
                'name' => 'expires_at',
                'type' => 'datetime',
                'size' => null,
                'scale' => null,
                'notnull' => true,
                'default' => null,
                'autoinc' => false,
                'primary' => false,
            ],
            'user_id' => (object) [
                'name' => 'user_id',
                'type' => 'int',
                'size' => 10,
                'scale' => null,
                'notnull' => true,
                'default' => null,
                'autoinc' => false,
                'primary' => false,
            ],
            'created_at' => (object) [
                'name' => 'created_at',
                'type' => 'datetime',
                'size' => null,
                'scale' => null,
                'notnull' => true,
                'default' => null,
                'autoinc' => false,
                'primary' => false,
            ],
            'updated_at' => (object) [
                'name' => 'updated_at',
                'type' => 'datetime',
                'size' => null,
                'scale' => null,
                'notnull' => true,
                'default' => null,
                'autoinc' => false,
                'primary' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKey()
    {
        return [
            'id',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAutoinc()
    {
        return 'id';
    }

    /**
     * @inheritdoc
     */
    public function getColDefaults()
    {
        return [
            'id' => null,
            'value' => null,
            'expires_at' => null,
            'user_id' => null,
            'created_at' => null,
            'updated_at' => null,
        ];
    }
}