# Firebase Cloud Messaging for PHP

Send Google Firebase Cloud messages with PHP.

## Getting started

Before you begin, make sure you have setup a Google Firebase project and have downloaded the JSON file.

Then instantiate a Messaging:

```php
new Messaging(new MyCache(), '/path/to/file.json', Log::getLogger())
```

Arguments:
- A `CacheInterface` implementation - see an example implementation for Laravel called `LaravelCache` in the `Models` directory.
- The path to the Google Firebase JSON file.
- An optional `Psr\Log\LoggerInterface` implementation. If you handle it a logger, logging will occur. This is handy for debugging purposes.

Now you can call the different methods on it.

## Sending messages to specific devices

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