<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    public function project()
    {
        return $this->belongsTo('App\Project');
    }
}
