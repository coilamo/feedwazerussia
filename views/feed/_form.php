<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\datetime\DateTimePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Feed */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="feed-form">

    <?php $form = ActiveForm::begin([
        'action' =>['feed/create'],
        'id' => 'feed-form',
        'options' => [
            'style' => $hide ? 'display: none;' : '',
        ]
    ]); ?>
    
    <div class="row">
        <span style="float:left; width: 47%;">
            <?= $form->field($model, 'polyline')->textarea(['maxlength' => true, 'rows' => 2]) ?>
            <?= $form->field($model, 'starttime')->widget(DateTimePicker::classname(), [
                    'options' => ['placeholder' => Yii::t('app/feed', 'Enter event start time ...')],
                    'type' => DateTimePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-ddThh:ii:ss'
                    ]
                ]) ?>
            <?= $form->field($model, 'endtime')->widget(DateTimePicker::classname(), [
                    'options' => ['placeholder' => Yii::t('app/feed', 'Enter event end time ...')],
                    'type' => DateTimePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-ddThh:ii:ss'
                    ]
                ]) ?>
   
        </span>
        <span style="float:right; width: 47%;">
            <?= $form->field($model, 'direction')->dropDownList([
                        'BOTH_DIRECTIONS' => Yii::t('app/feed', 'Both directions'),
                        'NORTH' => Yii::t('app/feed', 'To North'),
                        'SOUTH' => Yii::t('app/feed', 'To South'),
                        'EAST' => Yii::t('app/feed', 'To East'),
                        'WEST' => Yii::t('app/feed', 'To West'),
                        'NORTH_WEST' => Yii::t('app/feed', 'To North-West'),
                        'NORTH_EAST' => Yii::t('app/feed', 'To North-East'),
                        'SOUTH_EAST' => Yii::t('app/feed', 'To South-East'),
                        'SOUTH_WEST' => Yii::t('app/feed', 'To South-West'),
                        'ONE_DIRECTION' => Yii::t('app/feed', 'One direction')],
                    ['prompt' => Yii::t('app/feed', 'Select direction'), 'maxlength' => true, 'minlenght' => true]) ?>
            <?= $form->field($model, 'type')->dropDownList($allowedTypes, ['prompt' => Yii::t('app/feed', 'Select event type'), 'maxlength' => true, 'minlenght' => true]) ?>
            <?= $form->field($model, 'subtype')->dropDownList([], ['style' => 'display: none']) ?>
        </span>
    </div>
    <div class="row">
        <span style="float:left; width: 47%;">
            <?= $form->field($model, 'description')->textarea(['maxlength' => true, 'rows' => 6]) ?>
   
        </span>
        <span style="float:right; width: 47%;">
            <?= $form->field($model, 'comment')->textarea(['maxlength' => true, 'rows' => 6]) ?>
        </span>
    </div>
    <div class="row">
        <?= $form->field($model, 'street')->textInput(['maxlength' => true]) ?>
    </div>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
