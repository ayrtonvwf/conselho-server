<?php
namespace Conselho\DataSource\ObservationTopic;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\School\SchoolMapper;
use Conselho\DataSource\CouncilObservationTopic\CouncilObservationTopicMapper;

/**
 * @inheritdoc
 */
class ObservationTopicMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('school', SchoolMapper::CLASS)->on(['school_id' => 'id']);
        $this->oneToMany('council_observation_topics', CouncilObservationTopicMapper::CLASS)->on(['id' => 'observation_topic_id']);
    }
}
