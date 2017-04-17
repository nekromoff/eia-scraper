<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{

    public function companies()
    {
        return $this->hasMany('App\ProjectsCompany');
    }

    public function institutions()
    {
        return $this->hasMany('App\ProjectInstitution');
    }

    public function regions()
    {
        return $this->hasMany('App\ProjectsRegion');
    }

    public function districts()
    {
        return $this->hasMany('App\ProjectsDistrict');
    }

    public function localities()
    {
        return $this->hasMany('App\ProjectsLocality');
    }

    public function documents()
    {
        return $this->hasMany('App\Document');
    }
}
