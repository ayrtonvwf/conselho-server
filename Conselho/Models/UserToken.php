<?php
namespace Conselho\Models;
use Purekid\Mongodm\Model;

class UserToken extends Model
{
    static $collection = 'user_token';

    protected static $attrs = [
        'value' => ['type' => Model::DATA_TYPE_STRING],
        'user_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\User'],
        'expires_at' => ['type' => Model::DATA_TYPE_DATE],
        'updated_at' => ['type' => Model::DATA_TYPE_DATE]
    ];

    protected function __preSave() {
        $this->updated_at = new DateTime();
    }
}