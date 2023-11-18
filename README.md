# Firebase Cloud Messaging for PHP

Send Google Firebase Cloud messages with PHP.

## Getting started

First make sure you have a Firebase project. See https://firebase.google.com/docs/admin/setup.

Then create a Messaging instance:

```php
$messaging = new Messaging(new MyCache(), '/path/to/file.json', Log::getLogger());
```

The arguments:

1. An instance of a `Mve\FcmPhp\Models\CacheInterface` implementation. This is a simple interface that defines 4 methods: `get`, `put`, `forget` and `flush` and is used to cache the access token.
2. The path to the Google Firebase private key file in JSON format. Note: keep this file private!
3. An optional `Psr\Log\LoggerInterface` implementation if you want to see some logging about the requests and responses. It's best to enable this only for debugging purposes.

Now you can call the different methods on the `Messaging` instance.

## Sending messages to devices

```php
// Create messages. The first argument is the ID - this can be used to map the result to the specific message after sending.
$messages = [
    new TokenMessage(1, 'token1', "Content of notification", "Title of notification"),
    new TokenMessage(2, 'token1', "Content of notification", "Title of notification")
];
// Send the messages
$sendResult = $messaging->sendAll($messages);
// Check the result
// $sendResult->sent contains [ID => Google Firebase ID]
// $sendResult->unregistered contains [ID => FcmError]
// $sendResult->errors contains [ID => FcmError]
```

The `sendAll` method uses HTTP/2 multiplexing, so it is very fast and can be used to send alot of notifications.

## Testing

First make sure to set your Google JSON file to the `JSON_FILE` environment variable:

`export JSON_FILE="/path/to/file.json"`

If you want to test one or more valid registration tokens, also set the `TOKENS` environment variable:

`export TOKENS="token1, token2"`

### Test getting a valid access token

`./vendor/bin/phpunit tests/AccessTokenHandlerTest.php`

### Test invalid registration tokens

`./vendor/bin/phpunit tests/MessagingWithoutEnvTokenTest.php`

### Test valid registration tokens

Only works if you have set the `TOKENS` environment variable.

`./vendor/bin/phpunit tests/MessagingWithEnvTokenTest.php`