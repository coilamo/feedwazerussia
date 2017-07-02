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
        'id' => 'feed-form',
        'options' => [
            'style' => $hide ? 'display: none;' : '',
        ]
    ]); ?>
    
    <div class="row">
        <span style="float:left; width: 47%;">
            <?= $form->field($model, 'polyline')->textarea(['maxlength' => true, 'rows' => 2]) ?>
            <?= $form->field($model, 'starttime')->widget(DateTimePicker::classname(), [
                    'options' => ['placeholder' => 'Enter event start time ...'],
                    'type' => DateTimePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-ddThh:ii:ss'
                    ]
                ]) ?>
            <?= $form->field($model, 'endtime')->widget(DateTimePicker::classname(), [
                    'options' => ['placeholder' => 'Enter event end time ...'],
                    'type' => DateTimePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-ddThh:ii:ss'
                    ]
                ]) ?>
   
        </span>
        <span style="float:right; width: 47%;">
            <?= $form->field($model, 'direction')->dropDownList([
                        'BOTH_DIRECTIONS' => 'В обе стороны',
                        'NORTH' => 'На север',
                        'SOUTH' => 'На юг',
                        'EAST' => 'На восток',
                        'WEST' => 'На запад',
                        'NORTH_WEST' => 'На северо-запад',
                        'NORTH_EAST' => 'На северо-восток',
                        'SOUTH_EAST' => 'На юго-восток',
                        'SOUTH_WEST' => 'На юго-запад',
                        'ONE_DIRECTION' => 'В одну сторону'],
                    ['prompt' => 'Выберите направление', 'maxlength' => true, 'minlenght' => true]) ?>
            <?= $form->field($model, 'type')->dropDownList([
                'CHIT_CHAT' => 'Чат',
                'POLICE' => 'Полиция',
                'JAM' => 'Пробка',
                'ACCIDENT' => 'Авария',
                'CONSTRUCTION' => 'Ремонт',
                'HAZARD' => 'Опасность',
                'ROAD_CLOSED' => 'Перекрытие',
                ], ['prompt' => 'Выберите тип события', 'maxlength' => true, 'minlenght' => true]) ?>
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
