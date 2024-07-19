<?php

namespace Bo\LaravelQueryExecute\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Query extends Model
{
    use SoftDeletes;

    protected $table = 'queries';

    protected $fillable = [
        'name',
        'description',
        'query'
    ];
}
