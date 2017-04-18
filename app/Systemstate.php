<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Systemstate extends Model
{
    protected $table = 'systemstate';
    protected $fillable = ['key', 'value'];
}
