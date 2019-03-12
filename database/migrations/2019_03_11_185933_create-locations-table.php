<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('locations', function (Blueprint $table) {
			$table->increments('id');
			$table->decimal('lng', 10, 6);
			$table->decimal('lat', 10, 6);
			$table->smallInteger('city_id');
			$table->smallInteger('region_id');
//			$table->foreign('city_id')->references('id')->on('cities');
//			$table->foreign('region_id')->references('id')->on('regions');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('locations');
	}
}
