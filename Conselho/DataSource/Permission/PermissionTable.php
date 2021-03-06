<?php
/**
 * This file was generated by Atlas. Changes will be overwritten.
 */
namespace Conselho\DataSource\Permission;

use Atlas\Orm\Table\AbstractTable;

/**
 * @inheritdoc
 */
class PermissionTable extends AbstractTable
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'permission';
    }

    /**
     * @inheritdoc
     */
    public function getColNames()
    {
        return [
            'id',
            'name',
            'reference',
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
            'name' => (object) [
                'name' => 'name',
                'type' => 'varchar',
                'size' => 50,
                'scale' => null,
                'notnull' => true,
                'default' => null,
                'autoinc' => false,
                'primary' => false,
            ],
            'reference' => (object) [
                'name' => 'reference',
                'type' => 'varchar',
                'size' => 50,
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
            'name' => null,
            'reference' => null,
            'created_at' => null,
            'updated_at' => null,
        ];
    }
}
