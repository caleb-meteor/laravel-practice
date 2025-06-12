# Laravel Practice

一个开箱即用的 Laravel 实践开发包，简化响应处理、过滤器、服务层和异常管理。

## 功能特性

- **标准化 JSON 响应**: 统一的 API 响应格式，包含请求 ID 追踪
- **查询过滤器**: 优雅且可复用的查询过滤系统
- **服务层**: 带有异常处理功能的基础服务类
- **异常管理**: 全面的异常处理，支持本地化消息
- **模型标准化**: 增强的 Eloquent 模型，支持标准化 JSON 序列化
- **请求上下文**: 自动请求 ID 生成和上下文管理
- **Artisan 命令**: 使用自定义命令生成过滤器和服务

## 系统要求

- PHP ^8.1
- Laravel ^12.0
- ext-json

## 安装

通过 Composer 安装包：

```bash
composer require caleb/laravel-practice
```

包会自动注册其服务提供者。

## 配置

### 发布语言文件

```bash
php artisan vendor:publish --tag=practice-lang
```

### 发布存根文件

```bash
php artisan vendor:publish --tag=practice-stubs
```

## 使用方法

### 1. 响应 Trait

在控制器中使用 `Response` trait 来标准化 API 响应：

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
            return $this->error('创建用户失败', 422);
        }
    }
}
```

响应格式：
```json
{
    "code": 200,
    "msg": "操作成功",
    "data": {...},
    "request_id": "uuid-字符串"
}
```

### 2. 查询过滤器

创建过滤器类：

```bash
php artisan practice:make:filter UserFilter
```

定义过滤器：

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

在模型中使用：

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

在控制器中应用过滤器：

```php
public function index(UserFilter $filter)
{
    $users = User::filter($filter)->paginate();
    return $this->success($users);
}
```

### 3. 服务层

创建服务类：

```bash
php artisan practice:make:service UserService
```

定义服务：

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
            $this->throwAppException('邮箱已存在', 422);
        }
        
        return User::create($data);
    }
    
    public function getUserById(int $id)
    {
        $user = User::find($id);
        
        if (!$user) {
            $this->throwAppException('用户未找到', 404);
        }
        
        return $user;
    }
}
```

在控制器中使用：

```php
public function store(Request $request)
{
    $user = UserService::instance()->createUser($request->validated());
    return $this->success($user);
}
```

### 4. 模型标准化

在模型中使用 `Standardization` trait：

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

优势：
- 使用 `JSON_UNESCAPED_UNICODE` 标志进行 JSON 序列化
- 标准化日期格式 (`Y-m-d H:i:s`)
- 从请求参数动态获取 `per_page`
- 内置 `filter` 作用域用于查询过滤

### 5. 异常处理

包提供自动异常处理，支持本地化消息：

- **ValidationException**: 返回 422 状态码和验证错误
- **AuthenticationException**: 返回 401 状态码和错误消息
- **PracticeException**: 返回自定义错误码和消息
- **ModelNotFoundException**: 返回 404 状态码和"资源未找到"消息
- **ThrottleRequestsException**: 返回 429 状态码和限流消息

自定义异常：

```php
// 应用异常（记录为 DEBUG 级别）
$this->throwAppException('自定义错误消息', 422, $附加数据);

// 外部服务异常（正常记录）
$this->throwExternalAppException('外部服务错误', 503);
```

### 6. 请求上下文

包自动为每个请求上下文添加唯一的请求 ID，并将其包含在所有响应中。这有助于请求追踪和调试。

## 中间件

包自动注册 `AddContext` 中间件，功能包括：
- 为每个请求生成唯一的 UUID
- 将其存储在 Laravel 的 Context 中
- 将其包含在所有 JSON 响应中

## 本地化

包支持多种语言。目前包含：

**英语 (`en`):**
- 系统成功/错误消息
- 资源未找到消息

**中文 (`zh-CN`):**
- 系统成功/错误消息
- 资源未找到消息

你可以发布并自定义这些消息：

```bash
php artisan vendor:publish --tag=practice-lang
```

## 贡献

欢迎贡献！请随时提交 Pull Request。

## 许可证

此包是根据 [MIT 许可证](LICENSE) 授权的开源软件。

## 作者

- **Caleb** - [caleb.meteor@gmail.com](mailto:caleb.meteor@gmail.com)

## 关键词

- Laravel
- 响应
- 请求 ID
- 服务
- 过滤器
- 异常处理器
- 模型标准化
