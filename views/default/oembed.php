<?php

use humhub\modules\admin\models\forms\OEmbedSettingsForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\Button;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use Yii;

/* @var $this View */
/* @var $content \humhub\modules\content\models\Content */
/* @var $maxwidth int|null */
/* @var $maxheight int|null */
/* @var $providers array */
/* @var $settings OEmbedSettingsForm */

// Register dynamic CSS for styles
$this->registerCss("
    .humhub-oembed-content {
        max-width: 100%;
        overflow: hidden;
    }
    .humhub-oembed-content > div {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px;
    }
");

// Register JS for tooltips
$this->registerJs(<<<JS
    function initializeTooltips() {
        $('[data-toggle="tooltip"]').tooltip();
    }
    initializeTooltips();
    $(document).on('pjax:end', initializeTooltips);
JS, View::POS_READY);

$contentUrl = Url::to(['/content/perma', 'id' => $content->id], true);
$authorUrl = $content->createdBy ? Url::to($content->createdBy->getUrl(), true) : '';
$containerUrl = $content->container ? Url::to($content->container->getUrl(), true) : '';

// Calculate dimensions based on maxwidth and maxheight
$width = $maxwidth ? min(max(intval($maxwidth), 100), 800) : 800;
$height = $maxheight ? min(max(intval($maxheight), 100), 600) : 600;
$title = $content->getContentDescription() ?: Yii::t('base', 'Untitled Content');
?>

<div class="humhub-oembed-content" style="width: <?= $width ?>px;">
    <div>
        <h4 style="margin-top: 0;"><?= Html::encode($title) ?></h4>
        <p>
            <?= Yii::t('base', 'Created by:') ?> 
            <?php if ($content->createdBy): ?>
                <a href="<?= Html::encode($authorUrl) ?>" target="_blank">
                    <?= Html::encode($content->createdBy->displayName) ?>
                </a>
            <?php else: ?>
                <?= Yii::t('base', 'Unknown') ?>
            <?php endif; ?>
        </p>
        <?php if ($content->container): ?>
            <p>
                <?= Yii::t('base', 'In:') ?> 
                <a href="<?= Html::encode($containerUrl) ?>" target="_blank">
                    <?= Html::encode($content->container->displayName) ?>
                </a>
            </p>
        <?php endif; ?>
        <p>
            <a href="<?= Html::encode($contentUrl) ?>" target="_blank">
                <?= Yii::t('base', 'View on {appName}', ['appName' => Html::encode(Yii::$app->name)]) ?>
            </a>
        </p>
    </div>
</div>

<div class="provider-actions text-right">
    <?= Html::a(Yii::t('AdminModule.settings', 'Add new provider'), Url::to(['oembed-edit']), ['class' => 'btn btn-success']); ?>
</div>

<h4><?= Yii::t('AdminModule.settings', 'Enabled OEmbed providers'); ?></h4>

<?php if (!empty($providers)): ?>
    <div id="oembed-providers" class="row">
        <?php foreach ($providers as $providerName => $provider): ?>
            <div class="oembed-provider-container col-xs-6 col-md-3">
                <div class="oembed-provider">
                    <div class="oembed-provider-name">
                        <span>
                            <?= Html::encode($providerName) ?>
                        </span>
                        <?php 
                        $query = [];
                        if (!empty($provider['endpoint'])) {
                            parse_str($provider['endpoint'], $query); 
                        }
                        ?>
                        <?php if (isset($query['access_token']) && empty($query['access_token'])): ?>
                            <span class="label label-danger label-error"
                                  data-toggle="tooltip" data-placement="right"
                                  title="<?= Yii::t('AdminModule.settings', 'Access token is not provided yet.') ?>">
                                <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?= Html::a(Yii::t('base', 'Edit'), Url::to(['oembed-edit', 'name' => $providerName]), ['data-method' => 'POST', 'class' => 'btn btn-xs btn-link']); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p><strong><?= Yii::t('AdminModule.settings', 'Currently no provider active!'); ?></strong></p>
<?php endif; ?>

<hr>

<?php $form = ActiveForm::begin() ?>
    <?= $form->field($settings, 'requestConfirmation')->checkbox() ?>
    <?= Button::primary(Yii::t('AdminModule.settings', 'Save'))->submit() ?>
<?php ActiveForm::end(); ?>
