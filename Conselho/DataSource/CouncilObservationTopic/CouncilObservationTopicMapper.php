<?php
namespace Conselho\DataSource\CouncilObservationTopic;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\Council\CouncilMapper;
use Conselho\DataSource\ObservationTopic\ObservationTopicMapper;

/**
 * @inheritdoc
 */
class CouncilObservationTopicMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('council', CouncilMapper::CLASS)->on(['council_id' => 'id']);
        $this->manyToOne('observation_topic', ObservationTopicMapper::CLASS)->on(['observation_topic_id' => 'id']);
    }
}
