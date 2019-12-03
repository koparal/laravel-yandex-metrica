<?php

namespace YandexMetrica\Providers;

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
        $configPath = __DIR__ . '/../config/yandex-metrica.php';

        $this->publishes( [$configPath => config_path('yandex-metrica.php') ], 'yandex-metrica');
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