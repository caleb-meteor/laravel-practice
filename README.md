# Laravel Practice

A Laravel practice development package, ready to use out of the box. It simplifies handling responses, filters, services, and exception management.

## Features

- **Standardized JSON Response**: Unified API response format with request ID tracking
- **Query Filters**: Elegant and reusable query filtering system
- **Service Layer**: Base service class with exception handling capabilities
- **Exception Management**: Comprehensive exception handling with localized messages
- **Model Standardization**: Enhanced Eloquent models with standardized JSON serialization
- **Request Context**: Automatic request ID generation and context management
- **Artisan Commands**: Generate filters and services with custom commands

## Requirements

- PHP ^8.1
- Laravel ^12.0
- ext-json

## Installation

Install the package via Composer:

```bash
composer require caleb/laravel-practice
```

The package will automatically register its service provider.

## Configuration

### Publish Language Files

```bash
php artisan vendor:publish --tag=practice-lang
```

### Publish Stubs

```bash
php artisan vendor:publish --tag=practice-stubs
```

## Usage

### 1. Response Trait

Use the `Response` trait in your controllers to standardize API responses:

```php
<?php

namespace App\Http\Controllers;

use Caleb\Practice\Response;
use Illuminate\Http\Controller;

class UserController extends Controller
{
    use Response;
    
    public function index()
    {
        $users = User::all();
        return $this->success($users);
    }
    
    public function store(Request $request)
    {
        try {
            $user = User::create($request->validated());
            return $this->success($user);
        } catch (Exception $e) {
            return $this->error('Failed to create user', 422);
        }
    }
}
```

Response format:
```json
{
    "code": 200,
    "msg": "success",
    "data": {...},
    "request_id": "uuid-string"
}
```

### 2. Query Filters

Create a filter class:

```bash
php artisan practice:make:filter UserFilter
```

Define your filter:

```php
<?php

namespace App\Filters;

use Caleb\Practice\QueryFilter;

class UserFilter extends QueryFilter
{
    public function name($name)
    {
        $this->query->where('name', 'like', "%{$name}%");
    }
    
    public function email($email)
    {
        $this->query->where('email', $email);
    }
}
```

Use in your model:

```php
<?php

namespace App\Models;

use Caleb\Practice\Standardization;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use Standardization;
}
```

Apply filters in controller:

```php
public function index(UserFilter $filter)
{
    $users = User::filter($filter)->paginate();
    return $this->success($users);
}
```

### 3. Service Layer

Create a service class:

```bash
php artisan practice:make:service UserService
```

Define your service:

```php
<?php

namespace App\Services;

use Caleb\Practice\Service;
use App\Models\User;

class UserService extends Service
{
    public function createUser(array $data)
    {
        if (User::where('email', $data['email'])->exists()) {
            $this->throwAppException('Email already exists', 422);
        }
        
        return User::create($data);
    }
    
    public function getUserById(int $id)
    {
        $user = User::find($id);
        
        if (!$user) {
            $this->throwAppException('User not found', 404);
        }
        
        return $user;
    }
}
```

Use in controller:

```php
public function store(Request $request)
{
    $user = UserService::instance()->createUser($request->validated());
    return $this->success($user);
}
```

### 4. Model Standardization

Use the `Standardization` trait in your models:

```php
<?php

namespace App\Models;

use Caleb\Practice\Standardization;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use Standardization;
    
    protected $casts = [
        'settings' => 'array',
        'created_at' => 'datetime',
    ];
}
```

Benefits:
- JSON serialization with `JSON_UNESCAPED_UNICODE` flag
- Dynamic `per_page` from request parameters
- Built-in `filter` scope for query filtering

### 5. Exception Handling

The package provides automatic exception handling with localized messages:

- **ValidationException**: Returns 422 with validation errors
- **AuthenticationException**: Returns 401 with error message
- **PracticeException**: Returns custom error code and message
- **ModelNotFoundException**: Returns 404 with "Resource not found" message
- **ThrottleRequestsException**: Returns 429 with rate limit message

Custom exceptions:

```php
// Application exceptions (logged as DEBUG level)
$this->throwAppException('Custom error message', 422, $additionalData);

// External service exceptions (logged normally)
$this->throwExternalAppException('External service error', 503);
```

### 6. Request Context

The package automatically adds a unique request ID to each request context and includes it in all responses. This helps with request tracking and debugging.

## Middleware

The package automatically registers the `AddContext` middleware which:
- Generates a unique UUID for each request
- Stores it in Laravel's Context
- Includes it in all JSON responses

## Localization

The package supports multiple languages. Currently included:

**English (`en`):**
- System success/error messages
- Resource not found messages

**Chinese (`zh-CN`):**
- 系统成功/错误消息
- 资源未找到消息

You can publish and customize these messages:

```bash
php artisan vendor:publish --tag=practice-lang
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Author

- **Caleb** - [caleb.meteor@gmail.com](mailto:caleb.meteor@gmail.com)

## Keywords

- Laravel
- Response
- Request ID
- Service
- Filter
- Exception Handler
- Model Standardization
