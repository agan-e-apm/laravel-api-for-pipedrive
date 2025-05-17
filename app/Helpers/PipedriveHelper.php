<?php

namespace App\Helpers;

class PipedriveHelper
{
    public static function fetchStripeData($email)
    {
        if (!$email) return ['error' => 'Email is required'];

        $url = 'https://octopus-app-3hac5.ondigitalocean.app/api/stripe_data?email=' . urlencode($email);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['error' => 'Internal API returned HTTP ' . $httpCode];
        }

        $data = json_decode($response, true);

        if (isset($data['error'])) {
            return ['error' => $data['error']];
        }

        return $data;
    }
}
