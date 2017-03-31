<?php
require 'vendor/autoload.php';

use \League\OAuth2\Client\Provider\GenericProvider as GenericProvider;
use \League\OAuth2\Client\Provider\Exception\IdentityProviderException as IdentityProviderException;
use \League\OAuth2\Client\Token\AccessToken as AccessToken;

session_start();

include_once 'config.php';

// Setup a provider using the configs
$provider = new GenericProvider([
	'clientId' => $clientId,
	'clientSecret' => $clientSecret,
	'redirectUri' => $redirectUri,
	'urlAuthorize' => $urlAuthorize,
	'urlAccessToken' => $urlAccessToken,
	'urlResourceOwnerDetails' => $urlResourceOwnerDetails
]);

// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {

    // Fetch the authorization URL from the provider; this returns the
    // urlAuthorize option and generates and applies any necessary parameters
    // (e.g. state).
    $authorizationUrl = $provider->getAuthorizationUrl();

    // Get the state generated for you and store it to the session.
    $_SESSION['oauth2state'] = $provider->getState();

    // Redirect the user to the authorization URL.
    header('Location: ' . $authorizationUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || (empty($_SESSION['oauth2state'])) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);

    echo 'Invalid State. Click <a href="' . $_SERVER['SCRIPT_NAME'] . '">here</a> to start again.';
    exit;

} elseif (empty($_SESSION['accessToken'])) {

    try {
        // Try to get an access token using the authorization code grant.
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code'],
            'scope' => 'attendee_attendee'
        ]);

        $_SESSION['accessToken'] = $accessToken->jsonSerialize();

    } catch (IdentityProviderException $e) {
        // Failed to get the access token or user details.
        echo $e->getMessage() .' Click <a href="' . $_SERVER['SCRIPT_NAME'] . '">here</a> to start again.';
        exit;
    }

}

//if we have access_token , we can call the api 
if (isset($_SESSION['accessToken'])) {
	$accessToken = new AccessToken($_SESSION['accessToken']);

	if ($accessToken->hasExpired()) {
		echo 'AccessToken has expired';
		unset($_SESSION['accessToken']);
		exit;
	}

	$event_id = '25198085'; // example event_id

	try {
		// The provider provides a way to get an authenticated API request for
	    // the service, using the access token; it returns an object conforming
	    // to Psr\Http\Message\RequestInterface.

	    // Send a request with a Authorization header
	    // Authorization: Bearer 6F9eLbGil9fFduTbboiqPzZmIWk7P6urK4vFOkWe
	    // API: http://api-dev.gevme.com/api/events/25198085/attendees

		$request = $provider->getAuthenticatedRequest('GET',
			API_URL . '/events/' . $event_id . '/attendees', 
			$accessToken->getToken()
		);

		$response = $provider->getResponse($request);

		header('Content-Type: application/json');
		echo json_encode($response);
	} catch (IdentityProviderException $e) {
		print_r($e->getResponseBody());
		exit;
	}

}