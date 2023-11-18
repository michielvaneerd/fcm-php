# Firebase Cloud Messaging for PHP

Send Google Firebase Cloud messages with PHP.

## API documentation

See [API documentation](https://michielvaneerd.github.io/fcm-php/)

## Preparation

First make sure you have a Firebase project and you have downloaded the private key JSON file.
See https://firebase.google.com/docs/admin/setup for more information.

Each call to the The Google API has to be authenticated with an access token that is fetched from the Google API. These access tokens expire after an hour. This library will take care of getting an access token from the Google API and cache it for an hour. However it doesn't implement a caching mechanism itself, but it expects an implementation of the [`Mve\FcmPhp\Models\CacheInterface`](https://michielvaneerd.github.io/fcm-php/classes/Mve-FcmPhp-Models-CacheInterface.html) from the caller.

## Getting started

Sending messages is done with the `Messaging` class:

```php
$messaging = new Messaging(new MyCache(), '/path/to/file.json', Log::getLogger());
```

The arguments:

1. An instance of a `Mve\FcmPhp\Models\CacheInterface` implementation as explained earlier.
2. The path to the Google Firebase private key JSON file.
3. An optional `Psr\Log\LoggerInterface` implementation. This is only needed if you want to see the requests and responses logged.

## Sending messages to devices

The `sendAll` method uses HTTP/2 multiplexing. It's very fast and can be used to send a batch of notifications.

```php
// Create messages. The first argument is the ID - this can be used to map the result to the specific message after sending.
$messages = [
    new TokenMessage(1, 'token1', "Content of notification", "Title of notification"),
    new TokenMessage(2, 'token1', "Content of notification", "Title of notification")
];
// Send the messages
try {
    $sendResult = $messaging->sendAll($messages);
    foreach ($sendResult->getSent() as $messageId => $firebaseId) {
        // Successfully sent messages
    }
    foreach ($sendResult->getUnregistered() as $messageId => $fcmError) {
        // The tokens for these messages can be deleted safely.
    }
    foreach ($sendResult->getErrors() as $messageId => $fcmError) {
        // See the $fcmError for specifics about this error.
    }
} catch (\Mve\FcmPhp\Models\FcmException $ex) {
    // This is bad, but it's at least an error we got from the Google API
    die('Exception from the Google API: ' . $ex->fcmError->content);
} catch (\Exception $ex) {
    // This is also bad, and it's a non Google API exception, maybe network error?
    die('Some other exception: ' . $ex->getMessage());
}
```

## Testing

First make sure the environment variable `JSON_FILE` points to your Google Firebase private key JSON file:

`export JSON_FILE="/path/to/file.json"`

If you want to test one or more valid registration tokens, set then to the `TOKENS` environment variable:

`export TOKENS="token1, token2"`

If you already have an access token that you want to use during the tests, you can set it with:

`export ACCESS_TOKEN="my-access-token"`

### Test getting a valid access token

This will test getting an access token from the Google API.

`./vendor/bin/phpunit tests/AccessTokenHandlerTest.php`

### Test invalid registration tokens

This will test some invalid tokens.

`./vendor/bin/phpunit tests/MessagingWithoutEnvTokenTest.php`

### Test valid registration tokens

This will test one or more valid tokens.

Only works if you have set the `TOKENS` environment variable.

`./vendor/bin/phpunit tests/MessagingWithEnvTokenTest.php`