<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\FeedSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="feed-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'incident_id') ?>

    <?= $form->field($model, 'description') ?>

    <?= $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'incident') ?>

    <?php // echo $form->field($model, 'incidents') ?>

    <?php // echo $form->field($model, 'location') ?>

    <?= $form->field($model, 'polyline') ?>

    <?= $form->field($model, 'starttime') ?>

    <?= $form->field($model, 'endtime') ?>

    <?= $form->field($model, 'street') ?>

    <?= $form->field($model, 'type') ?>
    
    <?= $form->field($model, 'subtype') ?>

    <?= $form->field($model, 'direction') ?>

    <?php // echo $form->field($model, 'author_id') ?>

    <?php // echo $form->field($model, 'reference') ?>

    <?php // echo $form->field($model, 'source') ?>

    <?php // echo $form->field($model, 'location_description') ?>

    <?php // echo $form->field($model, 'name') ?>

    <?php // echo $form->field($model, 'parent_event') ?>

    <?php // echo $form->field($model, 'schedule') ?>

    <?php // echo $form->field($model, 'short_description') ?>

    <?php // echo $form->field($model, 'url') ?>

    <?php // echo $form->field($model, 'active') ?>

    <?php // echo $form->field($model, 'mail_send') ?>

    <?= $form->field($model, 'comment') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
