<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectsDistrict extends Model
{
    public function project()
    {
        return $this->belongsTo('App\Project');
    }
}
