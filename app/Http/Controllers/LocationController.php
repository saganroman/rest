<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Location;
use App\City;
use App\Region;
use Jcf\Geocode\Geocode;


class LocationController extends Controller
{
	const REGION_INGEX = 5;
	const CITY_INDEX = 3;
	const STREET_INDEX = 1;
	const BUILDING_NUMBER_INDEX = 0;
	const RESPONSE_STATUSES = ['OK' => 200, 'CREATED' => 201, 'BAD_REQUEST' => 400];

	public function getRegions($id = null)
	{
		if (isset($id)) {
			$locations = Location::where('region_id', $id)->with('regions')->get();
			return response()->json($this->getFormattedJson($locations), self::RESPONSE_STATUSES['OK']);
		} else {
			return response()->json(Region::all(), self::RESPONSE_STATUSES['OK']);
		}
	}

	public function locations(Request $request)
	{
		$params = $request->all();
		$result = [];
		if (isset($params['lat']) && isset($params['lng'])) {
			$lat = $params['lat'];
			$lng = $params['lng'];
			$locationExists = Location::where('lat', $lat)->where('lng', $lng)->first();
			if (!$locationExists) {
				$response = Geocode::make()->latLng($lat, $lng);
				$regionName = $response->raw()->address_components[self::REGION_INGEX]->long_name;
				$cityName = $response->raw()->address_components[self::CITY_INDEX]->long_name;
				$location = new Location();
				$cityExists = City::where('name', $cityName)->first();
				if (!$cityExists) {
					$city = City::create(['name' => $cityName]);
					$location->city_id = $city->id;
				} else {
					$location->city_id = $cityExists->id;
				}
				$regionExists = Region::where('name', $regionName)->first();
				if (!$regionExists) {
					$region = Region::create(['name' => $regionName]);
					$location->region_id = $region->id;
				} else {
					$location->region_id = $regionExists->id;
				}
				$location->lat = $lat;
				$location->lng = $lng;
				$location->full_address = $response->raw()->address_components[self::STREET_INDEX]->long_name . ' ' . $response->raw()->address_components[self::BUILDING_NUMBER_INDEX]->long_name;
				$location->save();
				$result['response'] = $location;
				$responseStatus = self::RESPONSE_STATUSES['CREATED'];
			} else {
				$result['response'] = $locationExists;
				$responseStatus = self::RESPONSE_STATUSES['OK'];
			}
		}
		return response()->json($result, $responseStatus);
	}

	private function getFormattedJson($locations)
	{
		$locationArray = [];
		foreach ($locations as $location) {
			$locationArray[] = ['lat' => $location->lat, 'lng' => $location->lng, 'city' => $location->cities[0]->name, 'full address' => $location->full_address];
		}
		return [
			'region' => $location->regions[0]->name,
			'locations' => $locationArray
		];
	}
}
