<?php
namespace Conselho\Models;
use Purekid\Mongodm\Model;

class GradeSubject extends Model
{
    static $collection = 'grade_subject';

    protected static $attrs = [
        'grade_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\Grade'],
        'subject_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\Subject'],
        'user_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\User'],
        'approved' => ['type' => Model::DATA_TYPE_BOOLEAN],
        'updated_at' => ['type' => Model::DATA_TYPE_DATE]
    ];

    protected function __preSave() {
        $this->updated_at = new DateTime();
    }
}