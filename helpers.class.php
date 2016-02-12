<?php

function getToken()
{
    $client = new Google_Client();
    $client->setApplicationName(GOOGLE_APPLICATION_NAME);
    $client->setClientId(GOOGLE_CLIENT_ID);

    $key = file_get_contents(GOOGLE_KEY_FILE);
    $cred = new Google_Auth_AssertionCredentials(
        GOOGLE_CLIENT_EMAIL,
        array(GOOGLE_APPLICATION_SCOPE),
        $key
    );

    $client->setAssertionCredentials($cred);

    if($client->getAuth()->isAccessTokenExpired()) {
        $client->getAuth()->refreshTokenWithAssertion($cred);
    }

    $service_token = json_decode($client->getAccessToken());
    return $service_token->access_token;
}

function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName(GOOGLE_APPLICATION_NAME);
    $client->setClientId(GOOGLE_CLIENT_ID);

    $key = file_get_contents(GOOGLE_KEY_FILE);
    $cred = new Google_Auth_AssertionCredentials(
        GOOGLE_CLIENT_EMAIL,
        array(GOOGLE_APPLICATION_SCOPE),
        $key
    );

    $client->setAssertionCredentials($cred);

    if($client->getAuth()->isAccessTokenExpired()) {
        $client->getAuth()->refreshTokenWithAssertion($cred);
    }

    return $client;

}

function debug($message)
{
    if (DEBUG==1) echo $message,'<br />'; flush();
}

?>