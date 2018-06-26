<?php
/**
 * This file was generated by Atlas. Changes will be overwritten.
 */
namespace Conselho\DataSource\Subject;

use Atlas\Orm\Table\AbstractTable;

/**
 * @inheritdoc
 */
class SubjectTable extends AbstractTable
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'subject';
    }

    /**
     * @inheritdoc
     */
    public function getColNames()
    {
        return [
            'id',
            'active',
            'name',
            'school_id',
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
            'active' => (object) [
                'name' => 'active',
                'type' => 'tinyint',
                'size' => 1,
                'scale' => null,
                'notnull' => true,
                'default' => '1',
                'autoinc' => false,
                'primary' => false,
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
            'school_id' => (object) [
                'name' => 'school_id',
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
            'active' => '1',
            'name' => null,
            'school_id' => null,
            'created_at' => null,
            'updated_at' => null,
        ];
    }
}
