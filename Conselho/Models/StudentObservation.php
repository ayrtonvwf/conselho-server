<?php
namespace Conselho\Models;
use Purekid\Mongodm\Model;

class StudentObservation extends Model
{
    static $collection = 'student_observation';

    protected static $attrs = [
        'user_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\User'],
        'student_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\Student'],
        'grade_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\Grade'],
        'subject_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\Subject'],
        'council_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\Council'],
        'description' => ['type' => Model::DATA_TYPE_STRING],
        'updated_at' => ['type' => Model::DATA_TYPE_DATE]
    ];

    protected function __preSave() {
        $this->updated_at = new DateTime();
    }
}