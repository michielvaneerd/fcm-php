# Technical information about Firebase

## Links to Google documentation for REST API

- https://developers.google.com/instance-id/reference/server - Manage topics and check registration tokens validity
- https://firebase.google.com/docs/cloud-messaging/send-message - How to construct messages
- https://github.com/firebase/firebase-admin-node - The nodejs client code, here you can see API endpoints, error codes, etc.

## How topics work and limits

- https://firebase.google.com/docs/cloud-messaging/js/topic-messaging
    - One app instance can be subscribed to no more than 2000 topics.
    - If you are using batch import to subscribe app instances, each request is limited to 1000 app instances.
    - The frequency of new subscriptions is rate-limited per project. If you send too many subscription requests in a short period of time, FCM servers will respond with a 429 RESOURCE_EXHAUSTED ("quota exceeded") response. Retry with exponential backoff.
- https://stackoverflow.com/questions/37646157/how-to-delete-a-topic-from-firebase-console-fcm - A topic is deleted once there are no subscriptions to it anymore. So this means we have to store topic tokens ourselves.
- Topics get created when you add tokens to it, so there is no API call to only create a topic. Use https://iid.googleapis.com/iid/v1:batchAdd to add tokens to a topic and at the same time create the topic.

## Errors

- https://cloud.google.com/resource-manager/docs/core_errors - Core errors
- https://firebase.google.com/docs/reference/fcm/rest/v1/ErrorCode - Specific FCM error codes (can override the core errors)
- https://developers.google.com/instance-id/reference/server#manage_relationship_maps_for_multiple_app_instances - Specific error codes for topic management

## Authentication

- https://developers.google.com/identity/protocols/oauth2/service-account - How to request for access tokens yourself without using a Google library. This involves creating JWT tokens and cryptographically signing.