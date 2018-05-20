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
        $this->manyToOne('councils', CouncilMapper::CLASS);
        $this->manyToOne('grades', GradeMapper::CLASS);
    }
}
