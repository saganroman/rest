<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
	protected $table = 'locations';
	public $timestamps = false;
	protected $fillable = [];

	public function regions()
	{
		return $this->hasMany('App\Region', 'id', 'region_id');
	}

	public function cities()
	{
		return $this->hasMany('App\City', 'id', 'city_id');
	}
}
