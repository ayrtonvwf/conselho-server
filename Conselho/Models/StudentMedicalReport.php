<?php
namespace Conselho\Models;
use Purekid\Mongodm\Model;

class StudentMedicalReport extends Model
{
    static $collection = 'student_grade';

    protected static $attrs = [
        'student_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\Student'],
        'subject_ids' => ['type' => Model::DATA_TYPE_REFERENCES, 'model' => 'Conselho\Models\Subject'],
        'description' => ['type' => Model::DATA_TYPE_STRING],
        'updated_at' => ['type' => Model::DATA_TYPE_DATE]
    ];

    protected function __preSave() {
        $this->updated_at = new DateTime();
    }
}