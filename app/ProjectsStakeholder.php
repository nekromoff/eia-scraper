<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectsStakeholder extends Model
{
    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    public function stakeholder()
    {
        return $this->belongsTo('App\Stakeholder');
    }
}
