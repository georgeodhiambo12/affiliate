<?php
function mpesaPayment($phone_number, $amount, $transaction_id, $referral_code = null) {
    $consumerKey = 'Bfrr3dH4lzYUJuZQVIGqG2ecAUj6iA4bEnrQtxFlJAiyTycy';
    $consumerSecret = 'hfYFTVkIbF4mVHg8rUjyI3i6m2bMU7UnAykDMX9n3NbglZ7g5vIzh75Qu7UTDZPQ';
    $shortcode = '4040545'; // Add your Paybill number here
    $passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2c2f5b7d49c4e1b228a1dbd66e30de3e'; // Example passkey for sandbox
    $lipa_na_mpesa_online_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

    $timestamp = date('YmdHis');
    $password = base64_encode($shortcode . $passkey . $timestamp);

    // Apply 10% discount if a referral code is provided
    if ($referral_code) {
        $amount = $amount * 0.9;
    }

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . base64_encode($consumerKey . ':' . $consumerSecret)]);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    $result = json_decode($response);
    $access_token = $result->access_token;

    curl_setopt($curl, CURLOPT_URL, $lipa_na_mpesa_online_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json', 'Authorization:Bearer ' . $access_token]);

    $curl_post_data = [
        'BusinessShortCode' => $shortcode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => $phone_number,
        'PartyB' => $shortcode,
        'PhoneNumber' => $phone_number,
        'CallBackURL' => 'https://tendersoko.com/callback_url', // Update with your callback URL
        'AccountReference' => $transaction_id,
        'TransactionDesc' => 'Payment for registration'
    ];

    $data_string = json_encode($curl_post_data);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    $response = curl_exec($curl);
    curl_close($curl);

    return json_decode($response, true);
}
?>
