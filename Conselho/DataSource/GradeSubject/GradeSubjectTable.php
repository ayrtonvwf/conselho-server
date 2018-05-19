<?php
/**
 * This file was generated by Atlas. Changes will be overwritten.
 */
namespace Conselho\DataSource\GradeSubject;

use Atlas\Orm\Table\AbstractTable;

/**
 * @inheritdoc
 */
class GradeSubjectTable extends AbstractTable
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'grade_subject';
    }

    /**
     * @inheritdoc
     */
    public function getColNames()
    {
        return [
            'id',
            'grade_id',
            'subject_id',
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
            'grade_id' => (object) [
                'name' => 'grade_id',
                'type' => 'int',
                'size' => 10,
                'scale' => null,
                'notnull' => true,
                'default' => null,
                'autoinc' => false,
                'primary' => false,
            ],
            'subject_id' => (object) [
                'name' => 'subject_id',
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
            'grade_id' => null,
            'subject_id' => null,
            'created_at' => null,
            'updated_at' => null,
        ];
    }
}
