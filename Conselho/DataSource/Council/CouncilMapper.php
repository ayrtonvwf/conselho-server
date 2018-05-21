<?php
namespace Conselho\DataSource\Council;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\CouncilGrade\CouncilGradeMapper;
use Conselho\DataSource\CouncilTopic\CouncilTopicMapper;
use Conselho\DataSource\Evaluation\EvaluationMapper;
use Conselho\DataSource\GradeObservation\GradeObservationMapper;
use Conselho\DataSource\School\SchoolMapper;
use Conselho\DataSource\StudentObservation\StudentObservationMapper;

/**
 * @inheritdoc
 */
class CouncilMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('school', SchoolMapper::CLASS)->on(['school_id' => 'id']);
        $this->oneToMany('council_topics', CouncilTopicMapper::CLASS)->on(['id' => 'council_id']);
        $this->oneToMany('council_grades', CouncilGradeMapper::CLASS)->on(['id' => 'council_id']);
        $this->oneToMany('student_observations', StudentObservationMapper::CLASS)->on(['id' => 'council_id']);
        $this->oneToMany('grade_observations', GradeObservationMapper::CLASS)->on(['id' => 'council_id']);
        $this->oneToMany('evaluations', EvaluationMapper::CLASS)->on(['id' => 'council_id']);
    }
}
