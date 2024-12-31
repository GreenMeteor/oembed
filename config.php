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
            'callback' => [Module::class, 'onAfterModuleEnable'],
        ],
        [
            'class' => UrlOembed::class,
            'event' => UrlOembed::EVENT_FETCH,
            'callback' => [Module::class, 'onUrlOembedFetch'],
        ],
    ],
    'urlManagerRules' => [
        'oembed' => 'oembed/default/oembed',
    ],
];
