# StudioIntern API Client Documentation

## Overview
The StudioIntern API Client is a PHP package that provides a simple and flexible way to interact with the StudioIntern API. It supports both API version 1 and 2, with features including OAuth authentication, multiple storage options, and various API endpoints.

**If you are not a StudioIntern customer, this package is probably useless for you.** See [the official StudioIntern website](https://www.studiointern.de).

## Installation

```bash
composer require studiointern/api-client
```

### Requirements

- PHP >=8.2
- Guzzle HTTP Client or php-curl extension
- PHP JSON extension
- OpenSSL extension

#### Important:

Since you can choose what http client to use (Guzzle or cURL), none of these requirements are 
marked as 'required' in the composer.json! That's why **they won't install automatically**. You 
have to make sure the presence of at least one of these libraries for yourself.

To use the Guzzle http client (recommended), run

```bash
composer require guzzlehttp/guzzle
```

To use the PHP cURL extension, it must be installed with your PHP. Depending on your operating system, 
it can be installed for example by

```bash
sudo apt install php-curl
```

(example for Debian and Ubuntu linux).

## Configuration

Find the configuration file config.example.php in the examples directory and copy it to 
config.php (it must not reside inside document root). Then adjust the new file to your needs. 
This file has the following structure (find doc inside the example file):

```php
return [
    'credentials' => [
        'oauth' => [
            'client_id'     => 'your_client_id',
            'client_secret' => 'your_client_secret',
            'grant_type'    => 'password',
            'scope'         => 'api:read api:write',
        ],
        'api_key'           => 'your_studiointern_api_key',
        'tenant_id'         => 'your_tenant_id',
    ],
    'headers' => [
        'Accept'            => 'application/json',
    ],
    'misc' => [
        'api_base_uri'  => 'https://api.studiointern.de',
        'storage'       => 'session',
        'storage_path'  => __DIR__ . '/../storage',
        'client_type'   => 'guzzle',
        'timezone'      => 'Europe/Berlin',
        'timeout'       => 30,
    ]
];
```

In the next step, we will hand the path to this file to the ApiConfig class.

## Basic Usage

### 1. Initialize the API Configuration

```php
use studiointern\Api\ApiConfig;

$config = new ApiConfig($path_to_config_file);
```

The $path_to_config_file parameter is optional. If omitted, the config file is expected to live 
at the same directory level as document root (but not inside document root).

### 2. Make API Requests

```php
// Set the endpoint you want to access
$config->setEndpoint('/pub/courses');

// Add any endpoint parameters if needed
$config->setEndpointParams('?param=value');

// or, if the endpoint expects the params as path
$config->setEndpointParams('/123');

// Create headers
$headers = $config->getDefaultHeaders();

// Create and execute the API client
$apiClient = $config->createApiClient(
    $config->getEndpointUrl(),
    $config->getEndpointMethod(),
    $headers,
    $post_data // Your request data
);

$response = $apiClient->execute();
$result = json_decode($response, true);
```

### 3. OAuth Authentication

Most endpoints require only the StudioIntern Api Key. But some require an OAuth authentication, 
**in addition**. So don't remove the Api Key when using OAuth endpoints!

You don't need to know if an endpoint requires OAuth2 authentication. The ApiConfig class 
knows and will tell you:

```php
// Argument '$path_to_config_file' is optional if you keep the default directory structure
$Config = new ApiConfig($path_to_config_file);  
if (true === $Config->requiresOauth()) {
    $oauthClient = new ApiOauthUser($Config);
    try {
        $token = $oauthClient->getToken();
        // the headers array is handed to the createApiClient method (see above)
        $headers['Authorization'] = 'Bearer ' . $token;
    }
    catch {
        // some exception handling
    }
}
```

Of course, you have to run this **before** calling the createApiClient() method.

## Available Endpoints

The API client supports the following endpoints:

| Endpoint | Method | Description | Version | OAuth Required | URL parameter |
|----------|--------|-------------|---------|----------------|---------------|
| `/pub/tariffs` | GET | Price Lists | 1 | No | none
| `/pub/summer_courses` | GET | Summer Courses | 1 | No | none
| `/pub/studio/signature` | GET | Studio Signature | 2 | No | none
| `/pub/schedule` | GET | Course Schedule (general week only) | 1 | No | (see below)
| `/pub/plans` | GET | Alternative Plans | 1 | No | none
| `/pub/nextcourses` | GET | Next Courses (real calendar only) | 1 | No | none
| `/pub/events` | GET | Upcoming Events (holidays) | 1 | No | none
| `/pub/courses` | GET | Courses | 1 | No | none
| `/pub/courses/styles` | GET | Course Groups (Dance Styles) | 2 | No | none
| `/pub/customer` | GET | Show Customer Registration Workflow | 2 | Yes | none
| `/pub/customer/register` | POST | Create New Customer | 2 | No | none
| `/pub/mailcheck/` | GET | Mail Check | 2 | No | /[email]/[predefined_check?]
| `/own/customer/` | GET | Customer List (Studio Owner) | 2 | Yes | none
| `/own/customer` | GET | Show Customer (Studio Owner) | 2 | Yes | /[customer_id]
| `/own/student/` | GET | Student List (Studio Owner) | 2 | Yes | none
| `/own/student` | GET | Show Student (Studio Owner) | 2 | Yes | /[student_id]
| `/own/account` | POST | Create Account Entry | 2 | Yes | none

The /pub/schedule has an optional parameter 'period'. See the official 
[StudioIntern API documentation](https://studiointern.stoplight.io/docs/si-api/) for more information.

Both the /own/customer and /own/student endpoints have optional parameters:

- 'per_page' (number of items per page, default: none - show all)
- 'page' (page number, default: 1)

## Storage Options

The API client supports two storage types:
- `session`: Uses PHP session storage
- `file`: Uses file-based storage

The storage is used to store the OAuth token for a limited time. The token is valid for 1 hour but will be 
refreshed automatically if you call an endpoint that requires OAuth.

## Error Handling

The API client uses exceptions for error handling:

```php
try {
    $response = $apiClient->execute();
    $result = json_decode($response, true);
    
    // Handle success based on API version
    if ($config->getVersion() === 1) {
        if (isset($result['message']) && 'OK' === $result['message']) {
            // Handle success
        }
    } else {
        if (isset($result['success']) && $result['success']) {
            // Handle success
        }
    }
} catch (\Exception $e) {
    // Handle error
    $error = $e->getMessage();
}
```

## Response Handling

The API has different response formats for version 1 and 2:

### Version 1 Response
```json
{
    "message": "OK",
    "data_key1": "value1",
    "data_key2": "value2"
}
```

### Version 2 Response
```json
{
    "success": true,
    "message": "Success message",
    "code": "success_code",
    "data": {
        "key1": "value1",
        "key2": "value2",
    }
}
```

## Best Practices

1. Always check the API version when handling responses
2. Implement proper error handling
3. Set appropriate timeouts for API requests
4. Validate input data before making API calls

## Examples

For more detailed examples and implementation guidelines, please refer to the example files in the `api-client/examples` directory.

## Support

For issues, bug reports, or feature requests, please create an issue in the repository, open a ticket 
at our [support site](https://studiointern.freshdesk.com/support/tickets/new) or contact the StudioIntern support team.

## License

This project is licensed under the MIT License - see the [LICENSE](../LICENSE) file for details.

## Author

Andreas Vent-Schmidt <avs@studiointern.de> 