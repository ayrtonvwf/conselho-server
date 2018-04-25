<?php
namespace Conselho\Models;
use Purekid\Mongodm\Model;

class TopicType extends Model
{
    static $collection = 'topic_type';

    protected static $attrs = [
        'name' => ['type' => Model::DATA_TYPE_STRING],
        'reference' => ['type' => Model::DATA_TYPE_STRING],
        'updated_at' => ['type' => Model::DATA_TYPE_DATE]
    ];

    protected function __preSave() {
        $this->updated_at = new DateTime();
    }
}