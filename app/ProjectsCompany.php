<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectsCompany extends Model
{
    protected $table = 'projects_companies';

    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    public function company()
    {
        return $this->belongsTo('App\Company');
    }
}
