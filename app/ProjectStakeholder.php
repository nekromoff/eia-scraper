<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectsStakeholder extends Model
{
    public function project()
    {
        return $this->belongsTo('App\Project');
    }
}
