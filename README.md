<p align="center"><img src="https://res.cloudinary.com/fayne/image/upload/v1787643269/fayne_logo.png" width="300"></p>

# Laravel Filament Sso

简体中文 | [English](docs/README.en.md)

单点登录，适用于 **PHP 8.4+、Laravel 13、Filament 5**。

## 功能与边界

支持 Okta、Microsoft Azure 和 LoginRadius 驱动及 Filament Panel 接入。各提供者默认关闭，不自动创建或映射任意外部账号。

## 安装

先在宿主 Composer 配置本私有包及所需 `fayne/*` 依赖的软件源，再执行：

```bash
composer require fayne/laravel-filament-sso
php artisan vendor:publish --tag=filament-sso-config
```

Laravel 自动发现服务提供者。仅提供字段的包无需注册 Panel 插件；业务包按下面的示例注册。依赖版本以 [composer.json](composer.json) 为准，不要复制开发宿主的私有仓库凭据。

## 使用与配置

```php
use Fayne\Sso\Filament\SsoPlugin;

$panel->plugins([SsoPlugin::make()]);
```

发布配置后，在 `config/filament-sso.php` 设置目标 `panel`、`identity.guard`、`identity.model`、`identity_mapping` 和提供者配置；密钥通过环境变量注入。目标账号必须已映射且处于启用状态。缺失或空身份声明会在账号查找前拒绝，`FilamentUser` 还必须有目标面板访问权限。

回调 URL 必须与身份服务商登记一致。使用本地应用 MFA 的账号仍需走密码与 MFA 登录，回调不替代本地挑战。Okta 支持 OAuth2/SAML，LoginRadius 支持 standard/OAuth2/SAML，按相应配置分支设置。

## 测试与上线

在本包目录执行 `composer install`、`composer test`、`composer format`。自动化检查不能代替宿主表单和真实环境验收。

## 合作与贡献

通过 Issue 描述需求或缺陷，提供复现步骤、版本和预期行为；提交合并请求时补充相关验证，并说明尚未验证的环境。请勿提交密钥或业务敏感数据。商业合作及授权请联系 [Fayne Guo](mailto:fayne.guo@gmail.com)。

## 许可证

采用[专有许可证](LICENSE.md)。未经授权不得复制、分发或使用；第三方依赖仍遵循各自许可证。

## 开发与安全

[开发与 CI 约定](CONTRIBUTING.md) · [安全报告](SECURITY.md)。运行 `composer check` 检查语法和格式；有独立套件时同时运行测试。包不提交 `composer.lock`。
