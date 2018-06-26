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
        $this->oneToMany('evaluations', EvaluationMapper::CLASS)->on(['id' => 'user_id']);
        $this->oneToMany('grade_observations', GradeObservationMapper::CLASS)->on(['id' => 'user_id']);
        $this->oneToMany('student_observations', StudentObservationMapper::CLASS)->on(['id' => 'user_id']);
        $this->oneToMany('roles', RoleMapper::CLASS)->on(['id' => 'user_id']);
        $this->oneToMany('user_tokens', UserTokenMapper::CLASS)->on(['id' => 'user_id']);
        $this->oneToMany('teachers', TeacherMapper::CLASS)->on(['id' => 'user_id']);
        $this->oneToMany('teacher_requests', TeacherRequestMapper::CLASS)->on(['id' => 'user_id']);
    }
}
