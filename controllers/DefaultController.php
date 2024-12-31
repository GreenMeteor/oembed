<?php

namespace humhub\modules\oembed\controllers;

use Yii;
use yii\helpers\Url;
use humhub\components\Controller;
use humhub\modules\content\models\Content;
use humhub\modules\rest\definitions\UserDefinitions;
use humhub\modules\rest\definitions\ContentDefinitions;
use humhub\modules\rest\definitions\SpaceDefinitions;

class DefaultController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['oembed'], 'roles' => ['@']],
                ],
            ],
        ]);
    }

    public function actionOembed()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $url = Yii::$app->request->get('url');

        $maxwidth = Yii::$app->request->get('maxwidth');
        $maxheight = Yii::$app->request->get('maxheight');

        if (!$url) {
            return $this->createErrorResponse('URL parameter is required.');
        }

        // Extract content ID from the URL
        $contentId = $this->extractContentId($url);

        if (!$contentId) {
            return $this->createErrorResponse('Invalid HumHub content URL.');
        }

        // Fetch content
        $content = Yii::$app->db->createCommand('SELECT * FROM content WHERE id = :contentId')
            ->bindValue(':contentId', $contentId)
            ->queryOne();

        if (!$content) {
            return $this->createErrorResponse('Content not found.');
        }

        // Check permissions
        if (!$content->canView()) {
            return $this->createErrorResponse('You do not have permission to view this content.');
        }

        // Create and return oEmbed response
        try {
            $response = $this->createOembedResponse($content, $maxwidth, $maxheight);
            return $response;
        } catch (\Exception $e) {
            return $this->createErrorResponse('Failed to generate oEmbed response.');
        }
    }

    private function extractContentId($url)
    {
        // Example for content/perma or content/view
        $baseUrl = preg_quote(Url::base(true), '/');

        // Match URLs like /content/perma?id=123 and /content/view?id=123
        if (preg_match("/^$baseUrl\/content\/(perma|view)\?id=(\d+)/", $url, $matches)) {
            return ['contentId' => $matches[2], 'containerId' => null];
        }
    
        // Match user-based post URLs: /u/<username>/post/view?id=123
        if (preg_match("/^$baseUrl\/u\/([^\/]+)\/post\/view\?id=(\d+)/", $url, $matches)) {
            // Find the user by username
            $user = \humhub\modules\user\models\User::findOne(['username' => $matches[1]]);
            if ($user) {
                return ['contentId' => $matches[2], 'containerId' => $user->id];
            }
            return null;
        }

        return null;
    }

    private function createOembedResponse(Content $content, $maxwidth, $maxheight)
    {
        $contentData = ContentDefinitions::getContent($content);

        $creator = $content->createdBy;
        $creatorData = $creator ? UserDefinitions::getUserShort($creator) : null;

        $spaceData = null;
        if ($content->getContainer() instanceof \humhub\modules\space\models\Space) {
            $space = $content->getContainer();
            $spaceData = SpaceDefinitions::getSpaceShort($space);
        }

        // Set default dimensions and apply constraints
        $width = $maxwidth ? min(intval($maxwidth), 800) : 800;
        $height = $maxheight ? min(intval($maxheight), 600) : 600;

        // Construct the oEmbed response
        $response = [
            'version' => '1.0',
            'type' => 'rich',
            'provider_name' => Yii::$app->name,
            'provider_url' => Url::base(true),
            'title' => $contentData['metadata']['title'] ?? 'No Title',
            'author_name' => $creatorData['display_name'] ?? 'Unknown',
            'author_url' => $creatorData['url'] ?? '',
            'html' => $this->renderPartial('oembed', [
                'content' => $content,
                'maxwidth' => $width,
                'maxheight' => $height,
            ]),
            'width' => $width,
            'height' => $height,
            'url' => $contentData['metadata']['url'] ?? $content->getUrl(true),
            'space' => $spaceData,
        ];

        return $response;
    }

    private function createErrorResponse($message)
    {
        Yii::$app->response->statusCode = 400;
        return ['error' => $message];
    }
}
