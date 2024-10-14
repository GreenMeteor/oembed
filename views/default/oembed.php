<?php

use yii\helpers\Url;
use humhub\libs\Html;

/* @var $content humhub\modules\content\models\Content */
/* @var $maxwidth int|null */
/* @var $maxheight int|null */

$contentUrl = Url::to(['/content/perma', 'id' => $content->id], true);
$authorUrl = $content->createdBy ? Url::to($content->createdBy->getUrl(), true) : '';
$containerUrl = $content->container ? Url::to($content->container->getUrl(), true) : '';

// Calculate dimensions based on maxwidth and maxheight
$width = $maxwidth ? min($maxwidth, 600) : 600;
$height = $maxheight ? min($maxheight, 400) : 400;
?>

<div class="humhub-oembed-content" style="width: <?= $width ?>px; max-width: 100%; overflow: hidden;">
    <div style="border: 1px solid #ddd; border-radius: 4px; padding: 10px;">
        <h4 style="margin-top: 0;"><?= Html::encode($content->getContentDescription()) ?></h4>
        <p>
            Created by: 
            <?php if ($content->createdBy): ?>
                <a href="<?= Html::encode($authorUrl) ?>" target="_blank">
                    <?= Html::encode($content->createdBy->displayName) ?>
                </a>
            <?php else: ?>
                Unknown
            <?php endif; ?>
        </p>
        <?php if ($content->container): ?>
            <p>
                In: 
                <a href="<?= Html::encode($containerUrl) ?>" target="_blank">
                    <?= Html::encode($content->container->displayName) ?>
                </a>
            </p>
        <?php endif; ?>
        <p>
            <a href="<?= Html::encode($contentUrl) ?>" target="_blank">
                View on <?= Html::encode(Yii::$app->name) ?>
            </a>
        </p>
    </div>
</div>