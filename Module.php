<?php

namespace humhub\modules\oembed;

use Yii;
use yii\helpers\Url;
use humhub\models\UrlOembed;
use humhub\components\Module as BaseModule;

class Module extends BaseModule
{
    public function init()
    {
        parent::init();

        // Register custom URL rules
        Yii::$app->urlManager->addRules([
            ['pattern' => 'oembed/endpoint', 'route' => 'oembed/default/oembed'],
        ], false);
    }

    public static function onAfterModuleEnable($event)
    {
        // Add this module's oEmbed provider to the global providers list
        $pattern = '/https?:\/\/' . str_replace('.', '\.', Yii::$app->request->hostInfo) . '\/content\/perma\/.+/';
        $endpoint = Url::to(['/oembed/default/oembed', 'url' => '%url%', 'maxwidth' => '%width%', 'maxheight' => '%height%'], true);
        
        UrlOembed::addProvider($pattern, $endpoint);
    }
}