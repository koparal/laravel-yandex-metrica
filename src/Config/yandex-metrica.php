<?php

return [
    'cache'         => env("YandexMetricaCache",60),      //Cache Time
    'counter_id'    => env("YandexMetricaCounterID",""),      //Counter id of Your Yandex Metrica Account
    'token'         => env("YandexMetricaToken",""),      //Oauth Token of Your Yandex Account
];