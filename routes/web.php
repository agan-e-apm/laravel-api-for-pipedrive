<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


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

    if (!$email) {
        return response('No email provided.', 400);
    }

    $data = PipedriveHelper::fetchStripeData($email);

    return view('pipedrive-panel', compact('email', 'data'));
});

Route::get('/', function (Illuminate\Http\Request $request) {
    $code = $request->query('code');

    if (!$code) {
        return response('No code found', 400);
    }

    // Exchange code for access token
    $response = Http::asForm()->post('https://oauth.pipedrive.com/oauth/token', [
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => 'https://laravel-api-for-pipedrive.onrender.com/',
        'client_id' => env('PIPEDRIVE_CLIENT_ID'),
        'client_secret' => env('PIPEDRIVE_CLIENT_SECRET'),
    ]);

    $data = $response->json();

    // Save $data['access_token'] securely, maybe in the DB or session
    return response()->json($data);
});

Route::get('/custom-panel', function (Illuminate\Http\Request $request) {
    $email = $request->query('email');

    if (!$email) {
        return 'No email provided';
    }

    // Use your helper or service to get transaction/invoice data based on $email
    $transactions = \App\Helpers\PipedriveHelper::fetchStripeData($email);

    // Render a view with the transaction data
    return view('pipedrive-panel', ['transactions' => $transactions]);
});

