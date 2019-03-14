<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{

	public function cities()
	{
		return $this->hasMany('App\City', 'id', 'city_id');
	}

	public function regions()
	{
		return $this->hasMany('App\Region', 'id', 'region_id');
	}
}
