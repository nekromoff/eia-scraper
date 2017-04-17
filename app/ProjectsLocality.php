<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectsLocality extends Model
{
    protected $table = 'projects_localities';

    public function project()
    {
        return $this->belongsTo('App\Project');
    }
}
