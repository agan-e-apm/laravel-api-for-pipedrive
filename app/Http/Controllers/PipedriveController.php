<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\PipedriveHelper;

class PipedriveController extends Controller
{
    public function getCustomerData(Request $request)
    {
        $email = $request->query('email');

        if (!$email) {
            return response()->json(['error' => 'Email is required'], 400);
        }

        $data = PipedriveHelper::fetchStripeData($email);

        if (isset($data['error'])) {
            return response()->json(['error' => $data['error']], 400);
        }

        return response()->json($data);
    }
}
