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

        if (!isset(Yii::$app->urlManager->rules['oembed'])) {
            Yii::$app->urlManager->addRules([
                ['pattern' => 'oembed', 'route' => 'oembed/default/oembed'],
            ], false);
        }
    }

    public static function onAfterModuleEnable($event)
    {
        // Get the name of the HumHub instance (for example, from settings or instance info)
        $host = parse_url(Url::base(true), PHP_URL_HOST);
        $pattern = '/'.preg_quote($host, '/').'/';
        $endpoint = Url::to(['/oembed/default/oembed', 'url' => '%url%', 'format' => 'json'], true, true);

        // Retrieve the HumHub instance name using Yii::$app->name
        $instanceName = Yii::$app->name;
        
        // Retrieve existing providers
        $existingProviders = UrlOembed::getProviders();

        // Add new custom oEmbed provider
        $newProvider = [
            'pattern' => $pattern,
            'endpoint' => $endpoint
        ];

        // Only add the new provider if it doesn't already exist
        if (!isset($existingProviders[Yii::$app->name])) {
            $existingProviders[Yii::$app->name] = $newProvider;
            UrlOembed::setProviders($existingProviders);
            
            UrlOembed::setProviders($existingProviders);
        }
    }

    public static function onUrlOembedFetch($event)
    {
        // Get the host and pattern from the base URL
        $host = parse_url(Url::base(true), PHP_URL_HOST);
        $pattern = '/'.preg_quote($host, '/').'/';
        $endpoint = Url::to(['/oembed/default/oembed', 'url' => '%url%', 'format' => 'json'], true, true);

        // Check if the provider already exists in the event's providers array
        if (!isset($event->providers[$pattern])) {
            // If not, add the provider with the endpoint and name
            $event->providers[$pattern] = [
                'endpoint' => $endpoint,
                'name' => Yii::$app->name,
            ];
        } else {
            // If the provider exists, preserve its name or set a default
            $providerName = $event->providers[$pattern]['name'] ?? Yii::$app->name;
        }
    }
}
