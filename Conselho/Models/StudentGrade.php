<?php
namespace Conselho\Models;
use Purekid\Mongodm\Model;

class StudentGrade extends Model
{
    static $collection = 'student_grade';

    protected static $attrs = [
        'grade_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\Grade'],
        'number' => ['type' => Model::DATA_TYPE_INTEGER],
        'student_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\Student'],
        'start' => ['type' => Model::DATA_TYPE_DATE],
        'updated_at' => ['type' => Model::DATA_TYPE_DATE]
    ];

    protected function __preSave() {
        $this->updated_at = new DateTime();
    }
}