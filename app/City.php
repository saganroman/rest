<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
	protected $table = 'cities';
	public $timestamps = false;
	protected $fillable = ['name'];

	public function locations()
	{
		return $this->belongsTo('App\Location');
	}
}
