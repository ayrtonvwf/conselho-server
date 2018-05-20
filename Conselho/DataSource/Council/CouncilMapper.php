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
        $this->manyToOne('school', SchoolMapper::CLASS);
        $this->oneToMany('council_topics', CouncilTopicMapper::CLASS);
        $this->oneToMany('council_grades', CouncilGradeMapper::CLASS);
        $this->oneToMany('student_observations', StudentObservationMapper::CLASS);
        $this->oneToMany('grade_observations', GradeObservationMapper::CLASS);
        $this->oneToMany('evaluations', EvaluationMapper::CLASS);
    }
}
