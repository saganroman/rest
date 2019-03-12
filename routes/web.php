<?php


Route::get('/regions','LocationController@getRegions');
Route::get('/regions/{id}','LocationController@getLocationByRegionId');
