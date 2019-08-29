<?php

namespace Conselho;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Builder
 */
class Model extends EloquentModel
{
    protected $casts = [
        'created_at' => 'datetime:c',
        'updated_at' => 'datetime:c'
    ];

    public static function getQuery() : Builder
    {
        return self::whereRaw(true);
    }
}
