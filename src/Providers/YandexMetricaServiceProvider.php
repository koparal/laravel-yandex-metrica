<?php

namespace YandexMetrika\Providers;

use YandexMetrica\Facades\YandexMetrica;
use Illuminate\Support\ServiceProvider;

class YandexMetricaServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/yandex-metrika.php';

        $this->publishes( [$configPath => config_path('yandex-metrika.php') ], 'yandex-metrika');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('YandexMetrica', \YandexMetrica\Foundation\YandexMetrica::class);
        $this->app->singleton(YandexMetrica::class);
    }
}