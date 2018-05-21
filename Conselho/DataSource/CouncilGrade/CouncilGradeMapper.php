<?php
namespace Conselho\DataSource\CouncilGrade;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\Council\CouncilMapper;
use Conselho\DataSource\Grade\GradeMapper;

/**
 * @inheritdoc
 */
class CouncilGradeMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('council', CouncilMapper::CLASS)->on(['council_id' => 'id']);
        $this->manyToOne('grade', GradeMapper::CLASS)->on(['grade_id' => 'id']);
    }
}
