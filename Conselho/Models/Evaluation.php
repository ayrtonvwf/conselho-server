<?php
namespace Conselho\Models;
use Purekid\Mongodm\Model;

class Evaluation extends Model
{
    static $collection = 'evaluation';

    protected static $attrs = [
        'user_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\User'],
        'student_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\Student'],
        'grade_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\Grade'],
        'subject_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\Subject'],
        'council_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\Council'],
        'topic_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\Topic'],
        'value' => ['type' => Model::DATA_TYPE_MIXED],
        'updated_at' => ['type' => Model::DATA_TYPE_DATE]
    ];

    protected function __preSave() {
        $this->updated_at = new DateTime();
    }
}