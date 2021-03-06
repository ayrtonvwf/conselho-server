<?php
/**
 * This file was generated by Atlas. Changes will be overwritten.
 */
namespace Conselho\DataSource\StudentObservation;

use Atlas\Orm\Table\AbstractTable;

/**
 * @inheritdoc
 */
class StudentObservationTable extends AbstractTable
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'student_observation';
    }

    /**
     * @inheritdoc
     */
    public function getColNames()
    {
        return [
            'id',
            'description',
            'council_id',
            'grade_id',
            'student_id',
            'subject_id',
            'user_id',
            'observation_topic_id',
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
            'description' => (object) [
                'name' => 'description',
                'type' => 'varchar',
                'size' => 1000,
                'scale' => null,
                'notnull' => true,
                'default' => null,
                'autoinc' => false,
                'primary' => false,
            ],
            'council_id' => (object) [
                'name' => 'council_id',
                'type' => 'int',
                'size' => 10,
                'scale' => null,
                'notnull' => true,
                'default' => null,
                'autoinc' => false,
                'primary' => false,
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
            'student_id' => (object) [
                'name' => 'student_id',
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
            'observation_topic_id' => (object) [
                'name' => 'observation_topic_id',
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
            'description' => null,
            'council_id' => null,
            'grade_id' => null,
            'student_id' => null,
            'subject_id' => null,
            'user_id' => null,
            'observation_topic_id' => null,
            'created_at' => null,
            'updated_at' => null,
        ];
    }
}
