<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Location;
use App\City;
use App\Region;
use Jcf\Geocode\Geocode;


class LocationController extends Controller
{
	const STREET_INDEX = "route";
	const BUILDING_NUMBER_INDEX = "street_number";
	const CITY_INDEX = "locality political";
	const REGION_INGEX = "administrative_area_level_1 political";
	const RESPONSE_STATUSES = ['OK' => 200, 'CREATED' => 201, 'BAD_REQUEST' => 400, 'NOT_FOUND' => 404];

	const REGION_DOES_NOT_EXISTS_ERROR_MESSEGE = 'Responce from google service does not containt region';
	const CITY_DOES_NOT_EXISTS_ERROR_MESSEGE = 'Responce from google service does not containt city';
	const STREET_DOES_NOT_EXISTS_ERROR_MESSEGE = 'Responce from google service does not containt street';
	const BUIILDING_NUMBER_DOES_NOT_EXISTS_ERROR_MESSEGE = 'Responce from google service does not containt building number';
	const RESPONCE_DOES_NOT_EXISTS_ERROR_MESSEGE = 'Google service does not responded';

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
				try {
					$response = Geocode::make()->latLng($lat, $lng);
				} catch (\Exception $e) {
					return response()->json($e->getMessage(), $e->getCode());
					exit;
				}
				if (!$response) {
					return response()->json(self::RESPONCE_DOES_NOT_EXISTS_ERROR_MESSEGE, self::RESPONSE_STATUSES['BAD_REQUEST']);
				}
				$address = $this->getAddressFromResponce($response);
				if (!isset($address[self::REGION_INGEX])) {
					return response()->json(self::REGION_DOES_NOT_EXISTS_ERROR_MESSEGE, self::RESPONSE_STATUSES['NOT_FOUND']);
				} else $regionName = $address[self::REGION_INGEX];
				if (!isset($address[self::CITY_INDEX])) {
					return response()->json(self::CITY_DOES_NOT_EXISTS_ERROR_MESSEGE, self::RESPONSE_STATUSES['NOT_FOUND']);
				} else $cityName = $address[self::CITY_INDEX];
				if (!isset($address[self::STREET_INDEX])) {
					return response()->json(self::STREET_DOES_NOT_EXISTS_ERROR_MESSEGE, self::RESPONSE_STATUSES['NOT_FOUND']);
				} else $street = $address[self::STREET_INDEX];
				if (!isset($address[self::BUILDING_NUMBER_INDEX])) {
					return response()->json(self::BUIILDING_NUMBER_DOES_NOT_EXISTS_ERROR_MESSEGE, self::RESPONSE_STATUSES['NOT_FOUND']);
				} else $buildingNumber = $address[self::BUILDING_NUMBER_INDEX];
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
				$location->full_address = $street . ' ' . $buildingNumber;
				$location->save();
				$result['response'] = $location;
				$result['status'] = self::RESPONSE_STATUSES['CREATED'];
			} else {
				$result['response'] = $locationExists;
				$result['status'] = self::RESPONSE_STATUSES['OK'];
			}
		}
		return response()->json($result, $result['status']);
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

	private function getAddressFromResponce($responce)
	{
		$data = array();
		foreach ($responce->response->address_components as $element) {
			$data[implode(' ', $element->types)] = $element->long_name;
		}
		return ($data);
	}
}
