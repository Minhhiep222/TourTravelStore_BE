<?php

namespace App\Http\Controllers;
// namespace App\Services;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Http;

class GoogleMapController extends Controller
{
    
    public function coordinateConvertion(Request $request) {
        try {
            $validatedData = $request->validate([
                'q' => 'required', 
            ], [
                'q.required' => 'Tour name is required',
            ]);
            $apiKey = "f4351059be8c44ceb4768dfe76395461";
            $response = Http::get("https://api.opencagedata.com/geocode/v1/json", [
                'q' => $validatedData['q'],
                'key' => $apiKey,
                'language' => 'en', 
            ]);
            $data = $response->json();
            if ($response->successful() && isset($data['results'][0])) {
                $geometry = $data['results'][0]['geometry'];
                return [
                    "message" => 'get coordinates succesful',
                    'lat' => $geometry['lat'],
                    'lng' => $geometry['lng'],
                ];
            }
          else {
            return response()->json([
                "error" => "location not found in map",
               
            ], 404);
          }
        }
            catch (\Exception $e) {
                return response()->json([
                    "message" => "An unexpected error occurred.",
                    "error" => $e->getMessage()
                ], 500);
            }
      
    }
public function reverseCoordinateConvertion(Request $request)
{
    try {
        $validatedData = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ], [
            'lat.required' => 'Latitude is required',
            'lng.required' => 'Longitude is required',
            'lat.numeric' => 'Latitude must be a numeric value',
            'lng.numeric' => 'Longitude must be a numeric value',
        ]);

        $apiKey = "f4351059be8c44ceb4768dfe76395461";

       
        $response = Http::get("https://api.opencagedata.com/geocode/v1/json", [
            'q' => $validatedData['lat'] . ',' . $validatedData['lng'], 
            'key' => $apiKey,
            'language' => 'en',
        ]);

        $data = $response->json();

        if ($response->successful() && isset($data['results'][0])) {
            $formattedAddress = $data['results'][0]['formatted']; 
            return response()->json([
                "message" => 'get location successful',
                'data' => $formattedAddress,
            ]);
        } else {
            return response()->json([
                "error" => "Location not found for the given coordinates",
            ], 404);
        }

    } catch (\Exception $e) {
        return response()->json([
            "message" => "An unexpected error occurred.",
            "error" => $e->getMessage()
        ], 500);
    }
}
public function getTimeTravel(Request $request)
{
    try {
        $validatedData = $request->validate([
            'startLat' => 'required|numeric',
            'startLon' => 'required|numeric',
            'endLat' => 'required|numeric',
            'endLon' => 'required|numeric',
        ], [
            'lat.required' => 'Latitude is required',
            'lat.numeric' => 'Latitude must be a numeric value',
            'lng.required' => 'Longitude is required',
            'lng.numeric' => 'Longitude must be a numeric value',
            'endLat.required' => 'Latend is required',
            'endLat.numeric' => 'Latend must be a numeric value',
            'endLon.required' => 'LongEnd is required',
            'endLon.numeric' => 'LongEnd must be a numeric value',
        ]);
        $startLat = $validatedData['startLat'];
        $startLon = $validatedData['startLon'];
        $endLat =  $validatedData['endLat'];
        $endLon = $validatedData['endLon'];
        $url = "http://router.project-osrm.org/route/v1/driving/{$startLon},{$startLat};{$endLon},{$endLat}?overview=false&geometries=polyline";
        $response = Http::get($url);
        if ($response->successful()) {
            $duration = $response->json()['routes'][0]['duration'];  
            $durationFormatted = floor($duration / 3600);
            return response()->json([
            'message' => 'calculate time travel sussesful',
            'data' => $durationFormatted,
            // 'data1' => $startLat,
            // 'data2' => $startLon,
            // 'data3' => $endLat,
            // 'data4' => $endLon
        ]);
        }
        return response()->json([ 
            "error" => "Unable to calculate travel time'"
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            "message" => "An unexpected error occurred.",
            "error" => $e->getMessage()
        ], 500);
    }   
}
        public function getDistanceTravel(Request $request)
        {
            try {
                $validatedData = $request->validate([
                    'startLat' => 'required|numeric',
                    'startLon' => 'required|numeric',
                    'endLat' => 'required|numeric',
                    'endLon' => 'required|numeric',
                ], [
                    'lat.required' => 'Latitude is required',
                    'lat.numeric' => 'Latitude must be a numeric value',
                    'lng.required' => 'Longitude is required',
                    'lng.numeric' => 'Longitude must be a numeric value',
                    'endLat.required' => 'Latend is required',
                    'endLat.numeric' => 'Latend must be a numeric value',
                    'endLon.required' => 'LongEnd is required',
                    'endLon.numeric' => 'LongEnd must be a numeric value',
                ]);
                $startLat = $validatedData['startLat'];
                $startLon = $validatedData['startLon'];
                $endLat =  $validatedData['endLat'];
                $endLon = $validatedData['endLon'];

            
            $url = "http://router.project-osrm.org/route/v1/driving/{$startLon},{$startLat};{$endLon},{$endLat}?overview=false&geometries=polyline"; 

            
            $response = Http::get($url);

            if ($response->successful()) {
                $distance = $response->json()['routes'][0]['legs'][0]['distance']; 
                $distanceInKm = $distance / 1000;
                $formattedNumber = number_format($distanceInKm, 2, '.', ',');
                return response()->json([
                    'message' => 'calculate distace travel sussesful',
                    'data' => $formattedNumber,
                ]);
            }
            return response()->json([ 
                "error" => "Unable to calculate distance"
            ], 404);
            } 
            catch (\Exception $e) {
                return response()->json([
                    "message" => "An unexpected error occurred.",
                    "error" => $e->getMessage()
                ], 500);
            }
            
        }

        }
