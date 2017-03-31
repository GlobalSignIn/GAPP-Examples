# Example OAuth2 Client

Example code to call the GEVME api using `League\OAuth2\Client` as an OAuth Client.

## Authorization Code Flow

1. Edit `config.php` and change the following details
	- clientId
	- clientSecret
	- redirectUri

2. Check `oauth2.php` for how to get an authorization_code and exchange it with an access_token.