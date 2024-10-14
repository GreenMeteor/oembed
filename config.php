<?php

use yii\helpers\Url;
use humhub\models\UrlOembed;
use humhub\modules\oembed\Module;
use humhub\components\ModuleManager;

return [
    'id' => 'oembed',
    'class' => Module::class,
    'namespace' => 'humhub\modules\oembed',
    'events' => [
        [
            'class' => ModuleManager::class,
            'event' => ModuleManager::EVENT_AFTER_MODULE_ENABLE,
            'callback' => [Module::class, 'onAfterModuleEnable']
        ],
        [
            'class' => UrlOembed::class,
            'event' => UrlOembed::EVENT_FETCH,
            'callback' => function ($event) {
                $pattern = '/https?:\/\/' . str_replace('.', '\.', Yii::$app->request->hostInfo) . '\/content\/perma\/.+/';
                $endpoint = Url::to(['/oembed/endpoint', 'url' => '%url%', 'maxwidth' => '%width%', 'maxheight' => '%height%'], true);
                $event->providers[$pattern] = $endpoint;
            },
        ],
    ],
];