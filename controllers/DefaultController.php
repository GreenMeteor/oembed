<?php

namespace humhub\modules\oembed\controllers;

use humhub\components\Controller;
use humhub\modules\content\models\Content;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use yii\helpers\Url;
use Yii;

class DefaultController extends Controller
{
    public function actionOembed()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $url = Yii::$app->request->get('url');
        $maxwidth = Yii::$app->request->get('maxwidth');
        $maxheight = Yii::$app->request->get('maxheight');

        if (!$url) {
            return $this->createErrorResponse('URL parameter is required.');
        }

        $contentId = $this->extractContentId($url);
        if (!$contentId) {
            return $this->createErrorResponse('Invalid HumHub content URL.');
        }

        $content = Content::findOne(['id' => $contentId]);
        if (!$content) {
            return $this->createErrorResponse('Content not found.');
        }

        return $this->createOembedResponse($content, $maxwidth, $maxheight);
    }

    private function extractContentId($url)
    {
        if (preg_match('/\/content\/perma\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function createOembedResponse($content, $maxwidth, $maxheight)
    {
        $contentUrl = Url::to(['/content/perma', 'id' => $content->id], true);
        $authorUrl = $content->createdBy ? Url::to($content->createdBy->getUrl(), true) : '';

        $width = $maxwidth ? min(intval($maxwidth), 800) : 800;
        $height = $maxheight ? min(intval($maxheight), 600) : 600;

        $response = [
            'version' => '1.0',
            'type' => 'rich',
            'provider_name' => Yii::$app->name,
            'provider_url' => Yii::$app->getRequest()->getHostInfo(),
            'title' => $content->getContentDescription(),
            'author_name' => $content->createdBy ? $content->createdBy->displayName : 'Unknown',
            'author_url' => $authorUrl,
            'html' => $this->renderPartial('oembed', [
                'content' => $content,
                'maxwidth' => $width,
                'maxheight' => $height
            ]),
            'width' => $width,
            'height' => $height,
            'url' => $contentUrl,
        ];

        if ($content->getContainer() instanceof Space) {
            $response['thumbnail_url'] = $content->getContainer()->getProfileImage()->getUrl();
        } elseif ($content->createdBy instanceof User) {
            $response['thumbnail_url'] = $content->createdBy->getProfileImage()->getUrl();
        }

        return $response;
    }

    private function createErrorResponse($message)
    {
        Yii::$app->response->statusCode = 400;
        return ['error' => $message];
    }
}