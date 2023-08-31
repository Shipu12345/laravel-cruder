# Laravel Cruder: Automatically generate crud functionality from a single command with Model name.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/:vendor_slug/:package_slug.svg?style=flat-square)](https://packagist.org/packages/:vendor_slug/:package_slug)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/:vendor_slug/:package_slug/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/:vendor_slug/:package_slug/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/:vendor_slug/:package_slug/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/:vendor_slug/:package_slug/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/:vendor_slug/:package_slug.svg?style=flat-square)](https://packagist.org/packages/:vendor_slug/:package_slug)
<!--delete-->

<!--/delete-->
## Installation

You can install the package via composer:

```bash
composer require shipu/laravel-cruder
```


You can generate files with:

```bash
php artisan crud:resource :ModelName
```

It Will Generate Corresponding:
    1) Model
    2) Controller
    3) Service
    4) Migration
    5) Views



- [:author_name](https://github.com/Shipu12345)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
