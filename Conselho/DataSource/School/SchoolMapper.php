<?php
namespace Conselho\DataSource\School;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\Council\CouncilMapper;
use Conselho\DataSource\Grade\GradeMapper;
use Conselho\DataSource\RoleType\RoleTypeMapper;
use Conselho\DataSource\Student\StudentMapper;
use Conselho\DataSource\Subject\SubjectMapper;
use Conselho\DataSource\Topic\TopicMapper;

/**
 * @inheritdoc
 */
class SchoolMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->oneToMany('grades', GradeMapper::CLASS)->on(['id' => 'school_id']);
        $this->oneToMany('subjects', SubjectMapper::CLASS)->on(['id' => 'school_id']);
        $this->oneToMany('students', StudentMapper::CLASS)->on(['id' => 'school_id']);
        $this->oneToMany('topics', TopicMapper::CLASS)->on(['id' => 'school_id']);
        $this->oneToMany('councils', CouncilMapper::CLASS)->on(['id' => 'school_id']);
        $this->oneToMany('role_types', RoleTypeMapper::CLASS)->on(['id' => 'school_id']);
    }
}
