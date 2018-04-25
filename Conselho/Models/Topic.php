<?php
namespace Conselho\Models;
use Purekid\Mongodm\Model;

class Topic extends Model
{
    static $collection = 'topic';

    protected static $attrs = [
        'name' => ['type' => Model::DATA_TYPE_STRING],
        'school_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\School'],
        'topic_type_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\TopicType'],
        'updated_at' => ['type' => Model::DATA_TYPE_DATE]
    ];

    protected function __preSave() {
        $this->updated_at = new DateTime();
    }
}