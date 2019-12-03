# Laravel Yandex Metrica

This package is prepared for Yandex Metrica

## Installation

```bash
composer require koparal/laravel-yandex-metrica
```


```bash
php artisan vendor:publish 
```

## Usage

```php
use YandexMetrica;

YandexMetrica::getGeneralAnalytics(7);
YandexMetrica::getGeneralAnalytics(7);
YandexMetrica::getOrganicData(7);
YandexMetrica::getDurationData(7);
YandexMetrica::getPageAnalytics(7);
YandexMetrica::getCountries(7);
YandexMetrica::getBrowserAndSystems(7);
```

## License
[MIT](https://choosealicense.com/licenses/mit/)