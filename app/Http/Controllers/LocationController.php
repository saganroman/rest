<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Location;
use App\City;
use App\Region;
use Jcf\Geocode\Geocode;

class LocationController extends Controller
{
	const REGION_INGEX = "administrative_area_level_1 political";
	const CITY_INDEX = "locality political";

	public function getRegions()
	{
		return response()->json(Region::all(), 201);
	}
	public function getLocationByRegionId($id)
	{
		$locations = Location::where('region_id',$id)->with('regions')->get();
		return response()->json($locations);
	}

	public function locations(Request $request)
	{
		$params = $request->all();
		$result = [];
		if (isset($params['lat']) && isset($params['lng'])) {
			$lat = $params['lat'];
			$lng = $params['lng'];
			$locationExists = Location::where('lat',$lat)->where('lng',$lng)->first();
			if (!$locationExists) {
				$response = Geocode::make()->latLng($lat, $lng);
				$address = $this->getAddressFromResponce($response);
				$regionName = $address[self::REGION_INGEX];
				$cityName = $address[self::CITY_INDEX];

				$location = new Location();
				$cityExists = City::where('name', $cityName)->first();
				if (!$cityExists) {
					$city = City::create(['name' =>$cityName]);
					$location->city_id = $city->id;
				} else {
					$location->city_id = $cityExists->id;
				}
				$regionExists = Region::where('name', $regionName)->first();
				if (!$regionExists) {
					$region = Region::create(['name' =>$regionName]);
					$location->region_id = $region->id;
				} else {
					$location->region_id = $regionExists->id;
				}
				$location->lat = $lat;
				$location->lng = $lng;
				$location->save();
				$result['response'] = $location;
			} else {
				$result['response'] = $locationExists;
			}
		}
		return response()->json($result, 201);
	}

	public function getAddressFromResponce($responce)
	{
		$data = array();
		foreach ($responce->response->address_components as $element) {
			$data[implode(' ', $element->types)] = $element->long_name;
		}
		return ($data);
	}




}
