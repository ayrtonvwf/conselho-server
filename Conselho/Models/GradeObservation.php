<?php
namespace Conselho\Models;
use Purekid\Mongodm\Model;

class GradeObservation extends Model
{
    static $collection = 'grade_observation';

    protected static $attrs = [
        'council_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\Council'],
        'user_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\User'],
        'grade_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\Grade'],
        'subject_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\Subject'],
        'description' => ['type' => Model::DATA_TYPE_STRING],
        'updated_at' => ['type' => Model::DATA_TYPE_DATE]
    ];

    protected function __preSave() {
        $this->updated_at = new DateTime();
    }
}