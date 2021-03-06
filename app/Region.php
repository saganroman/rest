<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
	protected $fillable = ['name'];

	public function locations()
	{
		return $this->hasMany('App\Location', 'region_id', 'id');
	}
}
