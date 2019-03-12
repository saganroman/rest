<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
	protected $table = 'regions';
	public $timestamps = false;
	protected $fillable = ['name'];
	protected $visible = ['name'];

	public function locations()
	{
		return $this->belongsTo('App\Location');
	}
}
