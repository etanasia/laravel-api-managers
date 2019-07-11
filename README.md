Welcome to Jawaraegov/laravel-api-managers!
===================
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/etanasia/laravel-api-managers/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/etanasia/laravel-api-managers/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/etanasia/laravel-api-managers/badges/build.png?b=master)](https://scrutinizer-ci.com/g/etanasia/laravel-api-managers/build-status/master)

Documents
-------------

Ini adalah package yang di gunakan untuk laravel api manager pemprov banten, dan package ini masih versi beta, found some bugs, create a patch or pull request.
update pull

> **Note:**

> - Package ini masih dalam tahap pengembangan.
> - package ini di gunakan untuk mengelola API KEY Provinsi Banten.
> - Package ini untuk laravel 5.4 keatas.

## Workflow
API Key Management ini dilengkapi dengan workflow management yang digunakan untuk melakukan proses permintaan sampai persetujuan API Key. See [Wokrflow managment](https://github.com/etanasia/workflows)

### Workflow State
> - Propose
> - Request
> - Approved
> - Rejected
> - Needs completed document
> - Document submitted

### Workflow Trasition
> - Propose to Propose
> - Propose to Request
> - Request to Approved
> - Request to Rejected
> - Approved to Rejected
> - Rejected to Approved
> - Request to Needs completed document
> - Needs completed document to Document submitted
> - Document submitted to Approved
> - Document submitted to Rejected


#### <i class="icon-file"></i> Install package

```sh
$ composer require jawaraegov/laravel-api-managers:dev-master
```
#### <i class="icon-file"></i> edit file config/app.php

tambahan class ini pada file config/app.php
```php
Jawaraegov\LaravelApiManagers\LaravelApiManagersServiceProvider::class,
```

#### <i class="icon-file"></i> running script vendor:publish

running vendor publish
```sh
php artisan vendor:publish
```

hasilnya
```sh
Copied Directory [/vendor/Jawaraegov/laravel-api-managers/src/config] To [/config]
Copied Directory [/vendor/Jawaraegov/laravel-api-managers/src/views] To [/resources/views/api_manager]
Copied Directory [/vendor/Jawaraegov/laravel-api-managers/src/controller] To [/app/Http/Controllers]
Copied Directory [/vendor/Jawaraegov/laravel-api-managers/src/models] To [/app]
Copied Directory [/vendor/Jawaraegov/laravel-api-managers/src/migrations] To [/database/migrations]
Copied Directory [/vendor/laravel/framework/src/Illuminate/Mail/resources/views] To [/resources/views/vendor/mail]
Publishing complete.
```
#### <i class="icon-file"></i> tambahkan route 

running script
```sh
php artisan laravel-api-managers:add-route
```

hasilnya akan menambahkan route resource di routes/web.php
```sh
Route::resource('api-manager', 'ApiManagerController');
```

#### <i class="icon-file"></i> Migrasi database 

running script
```sh
php artisan migrate
```

#### <i class="icon-file"></i> Running Modul 

browse dari browser anda
```sh
http://your_domain.dev/api-manager
```
#### <i class="icon-file"></i> Running Modul 

tambahkan pada .env anda parameter berikut
```php
URL_APIMANAGER=api.bantenprov.go.id
```
untuk production site
#### <i class="icon-file"></i> Happy Coding  \\(*i^)//
