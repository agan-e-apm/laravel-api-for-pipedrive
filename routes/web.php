<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


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
    $personId = $request->query('person_id');

    if (!$personId) {
        return response('Missing person_id', 400);
    }

    // 1. Get person details from Pipedrive API
    $accessToken = env('PIPEDRIVE_ACCESS_TOKEN');
    $personResponse = Http::withToken($accessToken)
        ->get("https://api.pipedrive.com/v1/persons/{$personId}");

    if ($personResponse->failed()) {
        return view('pipedrive-panel', [
            'email' => 'Unknown',
            'data' => ['error' => 'Failed to fetch person from Pipedrive.']
        ]);
    }

    $person = $personResponse->json('data');

    // 2. Get email from person data
    $email = $person['email'][0]['value'] ?? null;

    if (!$email) {
        return view('pipedrive-panel', [
            'email' => 'Unknown',
            'data' => ['error' => 'No email found for this person.']
        ]);
    }

    // 3. Fetch Stripe data from your internal API
    $stripeResponse = Http::get("https://octopus-app-3hac5.ondigitalocean.app/api/stripe_data", [
        'email' => $email
    ]);

    if ($stripeResponse->failed()) {
        return view('pipedrive-panel', [
            'email' => $email,
            'data' => ['error' => 'Failed to fetch Stripe data.']
        ]);
    }

    $data = $stripeResponse->json();

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

Route::get('/panel', function (Request $request) {
    $personId = $request->query('person_id');

    if (!$personId) {
        return response('Missing person_id', 400);
    }

    // Use your Pipedrive API token from .env
    $token = env('PIPEDRIVE_ACCESS_TOKEN');

    // Step 1: Fetch person info using person_id
    $response = Http::withToken($token)
        ->get("https://api.pipedrive.com/v1/persons/{$personId}");

    if (!$response->ok()) {
        return response('Failed to fetch contact from Pipedrive', 400);
    }

    $person = $response->json('data');
    $email = $person['email'][0]['value'] ?? null;

    if (!$email) {
        return response('No email found for this contact', 404);
    }

    // Step 2: Use email to fetch Stripe data from your external API
    $stripeRes = Http::get('https://octopus-app-3hac5.ondigitalocean.app/api/stripe_data', [
        'email' => $email
    ]);

    $data = $stripeRes->ok() ? $stripeRes->json() : ['error' => 'Stripe API error'];

    return view('pipedrive-panel', compact('email', 'data'));
});

Route::get('/api/pipedrive-panel-data', function (Request $request) {
    $personId = $request->query('person_id');

    if (!$personId) {
        return response()->json(['error' => 'Missing person_id'], 400);
    }

    $accessToken = env('PIPEDRIVE_ACCESS_TOKEN');

    // Get person details from Pipedrive
    $response = Http::withToken($accessToken)
        ->get("https://api.pipedrive.com/v1/persons/{$personId}");

    if (!$response->ok()) {
        return response()->json(['error' => 'Failed to fetch person data'], 500);
    }

    $person = $response->json('data');
    $email = $person['email'][0]['value'] ?? null;

    if (!$email) {
        return response()->json(['error' => 'No email found'], 404);
    }

    // Get Stripe data
    $stripeData = Http::get('https://octopus-app-3hac5.ondigitalocean.app/api/stripe_data', [
        'email' => $email
    ])->json();

    return response()->json(array_merge($stripeData, ['email' => $email]));
});

