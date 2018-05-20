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
        $this->oneToMany('grades', GradeMapper::CLASS);
        $this->oneToMany('subjects', SubjectMapper::CLASS);
        $this->oneToMany('students', StudentMapper::CLASS);
        $this->oneToMany('topics', TopicMapper::CLASS);
        $this->oneToMany('councils', CouncilMapper::CLASS);
        $this->oneToMany('role_types', RoleTypeMapper::CLASS);
    }
}
