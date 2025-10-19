# # TrackRequest

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**variant_key** | **string** | Variant key returned by evaluate (e.g. \&quot;A\&quot;, \&quot;v2\&quot;). |
**event_type** | **string** | Type of event (e.g. \&quot;success\&quot;, \&quot;failure\&quot;, \&quot;error\&quot;). |
**reward** | **float** | Numeric reward associated with event (e.g. 1.0 for conversion). Default 0. | [optional]
**context** | **array<string,mixed>** | Arbitrary context passed by SDK (user id, session, metadata). | [optional]
**created_at** | **\DateTime** | Event timestamp. If omitted, server time will be used. | [optional]
**dedup_key** | **string** | Optional idempotency key to deduplicate duplicate events from SDK retries. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
