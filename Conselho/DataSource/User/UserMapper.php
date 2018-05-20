<?php
namespace Conselho\DataSource\User;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\Evaluation\EvaluationMapper;
use Conselho\DataSource\GradeObservation\GradeObservationMapper;
use Conselho\DataSource\Role\RoleMapper;
use Conselho\DataSource\StudentObservation\StudentObservationMapper;
use Conselho\DataSource\Teacher\TeacherMapper;
use Conselho\DataSource\TeacherRequest\TeacherRequestMapper;
use Conselho\DataSource\UserToken\UserTokenMapper;

/**
 * @inheritdoc
 */
class UserMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->oneToMany('evaluations', EvaluationMapper::CLASS);
        $this->oneToMany('grade_observations', GradeObservationMapper::CLASS);
        $this->oneToMany('student_observations', StudentObservationMapper::CLASS);
        $this->oneToMany('roles', RoleMapper::CLASS);
        $this->oneToMany('user_tokens', UserTokenMapper::CLASS);
        $this->oneToMany('teachers', TeacherMapper::CLASS);
        $this->oneToMany('teacher_requests', TeacherRequestMapper::CLASS);
    }
}
