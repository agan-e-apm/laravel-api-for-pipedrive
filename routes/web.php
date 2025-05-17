<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Route::get('/api/pipedrive-customer-data', function (Request $request) {
//     $email = $request->query('email');
//     if (!$email) {
//         return response()->json(['error' => 'Email parameter is required'], 400);
//     }

//     $url = 'https://octopus-app-3hac5.ondigitalocean.app/api/stripe_data?email=' . urlencode($email);

//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     $response = curl_exec($ch);
//     $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//     curl_close($ch);

//     if ($httpCode !== 200) {
//         return response()->json(['error' => 'Internal API returned HTTP ' . $httpCode], $httpCode);
//     }

//     $data = json_decode($response, true);

//     if (isset($data['error'])) {
//         return response()->json(['error' => $data['error']], 400);
//     }

//     return response()->json($data);
// });

use App\Helpers\PipedriveHelper;

Route::get('/api/pipedrive-customer-data', function (Request $request) {
    $email = $request->query('email');
    return response()->json(PipedriveHelper::fetchStripeData($email));
});

Route::get('/pipedrive-panel', function (Request $request) {
    $email = $request->query('email');
    $data = PipedriveHelper::fetchStripeData($email);
    return view('pipedrive-panel', compact('email', 'data'));
});
