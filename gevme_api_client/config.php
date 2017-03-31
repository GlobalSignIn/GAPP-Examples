<?php

define('API_URL', 'http://api-dev.gevme.com/api');

$clientId = '';
$clientSecret = '';
$redirectUri = 'http://localhost/gevme_api_client/oauth2.php';

$urlAuthorize = API_URL . '/oauth/authorize';
$urlAccessToken = API_URL . '/oauth/access_token';
$urlResourceOwnerDetails = API_URL . '/oauth/resource';
