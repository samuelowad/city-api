<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CityController extends Controller
{
        private $geonamesUsername;

        public function __construct()
        {
            $this->geonamesUsername = config('services.geonames.username');
        }

    public function index()
    {
        $cities = City::all();
        return response()->json($cities);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'favorite' => 'boolean',
            'temperature' => 'nullable|numeric',
        ], [
            'name.required' => 'The name field is required.',
            'favorite.boolean' => 'The favorite field must be a boolean value.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $cityName = $request->input('name');
        $favoriteCity = $request->input('favorite');
        $temperature = $request->input('temperature');

        $geoResponse = Http::get("http://api.geonames.org/searchJSON", [
            'q' => $cityName,
            'maxRows' => 1,
            'username' => $this->geonamesUsername
        ]);
        if ($geoResponse->successful() && $geoResponse['totalResultsCount'] > 0) {

        try {
            DB::table('cities')->insert([
                'name' => $cityName,
                'favorite' => $favoriteCity,
                'temperature' => $temperature,
                'created_at' => Carbon::now(),
            ]);
            return response()->json(['message' => 'City added successfully'], 201);
        } catch (\Illuminate\Database\QueryException $e) {
           if ($e->errorInfo[0] === '23505') {
               return response()->json(['error' => 'City name already exists'], 400);
           }
           return response()->json(['error' => 'Failed to add city'], 500);
        }
        } else {
            return response()->json(['message' => 'City does not exist'], 404);
        }
    }

    public function show($id)
    {
        $city = City::find($id);

        if (!$city) {
            return response()->json(['message' => 'City not found'], 404);
        }

        return response()->json($city);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'favorite' => 'boolean',
            'temperature' => 'nullable|numeric',
        ], [
            'name.required' => 'The name field is required.',
            'favorite.boolean' => 'The favorite field must be a boolean value.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $city = City::find($id);

        if (!$city) {
            return response()->json(['message' => 'City not found'], 404);
        }

        $cityName = $request->input('name');
        $geoResponse = Http::get("http://api.geonames.org/searchJSON", [
            'q' => $cityName,
            'maxRows' => 1,
            'username' => $this->geonamesUsername
        ]);

        if ($geoResponse->successful() && $geoResponse['totalResultsCount'] > 0) {
            $city->name = htmlspecialchars($cityName, ENT_QUOTES, 'UTF-8');
            $city->favorite = $request->input('favorite');
            $city->temperature = $request->input('temperature');
            $city->save();
            return response()->json(['message' => 'City updated successfully', 'city' => $city]);
        } else {
            return response()->json(['message' => 'City does not exist'], 404);
        }
    }

    public function destroy($id)
    {
        $city = City::find($id);

        if (!$city) {
            return response()->json(['message' => 'City not found'], 404);
        }

        $city->delete();

        return response()->json(['message' => 'City deleted successfully']);
    }

    public function verifyCity($cityName)
    {
        $geoResponse = Http::get("http://api.geonames.org/searchJSON", [
            'q' => $cityName,
            'maxRows' => 1,
            'username' => $this->geonamesUsername
        ]);

        if ($geoResponse->successful() && $geoResponse['totalResultsCount'] > 0) {
            return response()->json(['valid' => true, 'data' => $geoResponse['geonames'][0]]);
        } else {
            return response()->json(['valid' => false, 'message' => 'City not found'], 404);
        }
    }
}
