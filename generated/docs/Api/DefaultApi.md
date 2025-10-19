# TogglrClient\DefaultApi

All URIs are relative to http://localhost:8090, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**getFeatureHealth()**](DefaultApi.md#getFeatureHealth) | **GET** /sdk/v1/features/{feature_key}/health | Get health status of feature (including auto-disable state) |
| [**reportFeatureError()**](DefaultApi.md#reportFeatureError) | **POST** /sdk/v1/features/{feature_key}/report-error | Report feature execution error (for auto-disable) |
| [**sdkV1FeaturesFeatureKeyEvaluatePost()**](DefaultApi.md#sdkV1FeaturesFeatureKeyEvaluatePost) | **POST** /sdk/v1/features/{feature_key}/evaluate | Evaluate feature for given context |
| [**sdkV1HealthGet()**](DefaultApi.md#sdkV1HealthGet) | **GET** /sdk/v1/health | Health check for SDK server |
| [**trackFeatureEvent()**](DefaultApi.md#trackFeatureEvent) | **POST** /sdk/v1/features/{feature_key}/track | Track event for a feature (impression / conversion / error / custom) |


## `getFeatureHealth()`

```php
getFeatureHealth($feature_key): \TogglrClient\Model\FeatureHealth
```

Get health status of feature (including auto-disable state)

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: ApiKeyAuth
$config = TogglrClient\Configuration::getDefaultConfiguration()->setApiKey('Authorization', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = TogglrClient\Configuration::getDefaultConfiguration()->setApiKeyPrefix('Authorization', 'Bearer');


$apiInstance = new TogglrClient\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$feature_key = 'feature_key_example'; // string

try {
    $result = $apiInstance->getFeatureHealth($feature_key);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->getFeatureHealth: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **feature_key** | **string**|  | |

### Return type

[**\TogglrClient\Model\FeatureHealth**](../Model/FeatureHealth.md)

### Authorization

[ApiKeyAuth](../../README.md#ApiKeyAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `reportFeatureError()`

```php
reportFeatureError($feature_key, $feature_error_report)
```

Report feature execution error (for auto-disable)

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: ApiKeyAuth
$config = TogglrClient\Configuration::getDefaultConfiguration()->setApiKey('Authorization', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = TogglrClient\Configuration::getDefaultConfiguration()->setApiKeyPrefix('Authorization', 'Bearer');


$apiInstance = new TogglrClient\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$feature_key = 'feature_key_example'; // string
$feature_error_report = new \TogglrClient\Model\FeatureErrorReport(); // \TogglrClient\Model\FeatureErrorReport

try {
    $apiInstance->reportFeatureError($feature_key, $feature_error_report);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->reportFeatureError: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **feature_key** | **string**|  | |
| **feature_error_report** | [**\TogglrClient\Model\FeatureErrorReport**](../Model/FeatureErrorReport.md)|  | |

### Return type

void (empty response body)

### Authorization

[ApiKeyAuth](../../README.md#ApiKeyAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdkV1FeaturesFeatureKeyEvaluatePost()`

```php
sdkV1FeaturesFeatureKeyEvaluatePost($feature_key, $request_body): \TogglrClient\Model\EvaluateResponse
```

Evaluate feature for given context

Returns feature evaluation result for given project and context. The project is derived from the API key.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: ApiKeyAuth
$config = TogglrClient\Configuration::getDefaultConfiguration()->setApiKey('Authorization', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = TogglrClient\Configuration::getDefaultConfiguration()->setApiKeyPrefix('Authorization', 'Bearer');


$apiInstance = new TogglrClient\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$feature_key = 'feature_key_example'; // string
$request_body = NULL; // array<string,mixed>

try {
    $result = $apiInstance->sdkV1FeaturesFeatureKeyEvaluatePost($feature_key, $request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->sdkV1FeaturesFeatureKeyEvaluatePost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **feature_key** | **string**|  | |
| **request_body** | [**array<string,mixed>**](../Model/mixed.md)|  | |

### Return type

[**\TogglrClient\Model\EvaluateResponse**](../Model/EvaluateResponse.md)

### Authorization

[ApiKeyAuth](../../README.md#ApiKeyAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdkV1HealthGet()`

```php
sdkV1HealthGet(): \TogglrClient\Model\HealthResponse
```

Health check for SDK server

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new TogglrClient\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);

try {
    $result = $apiInstance->sdkV1HealthGet();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->sdkV1HealthGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\TogglrClient\Model\HealthResponse**](../Model/HealthResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `trackFeatureEvent()`

```php
trackFeatureEvent($feature_key, $track_request)
```

Track event for a feature (impression / conversion / error / custom)

Send a feedback event related to a feature evaluation. Events are written to TimescaleDB (hypertable) and used for analytics, auto-disable and training MAB algorithms. The project is derived from the API key.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: ApiKeyAuth
$config = TogglrClient\Configuration::getDefaultConfiguration()->setApiKey('Authorization', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = TogglrClient\Configuration::getDefaultConfiguration()->setApiKeyPrefix('Authorization', 'Bearer');


$apiInstance = new TogglrClient\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$feature_key = 'feature_key_example'; // string
$track_request = {"variant_key":"A","event_type":"success","reward":0,"context":{"user.id":"123","country":"RU"}}; // \TogglrClient\Model\TrackRequest

try {
    $apiInstance->trackFeatureEvent($feature_key, $track_request);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->trackFeatureEvent: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **feature_key** | **string**|  | |
| **track_request** | [**\TogglrClient\Model\TrackRequest**](../Model/TrackRequest.md)|  | |

### Return type

void (empty response body)

### Authorization

[ApiKeyAuth](../../README.md#ApiKeyAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
