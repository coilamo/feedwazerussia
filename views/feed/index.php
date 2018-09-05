<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\FeedSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/feed', 'Reports');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="feed-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php if($isBulk) {
        if ($bulkAction == 'r') {
            if ($bulkResult) {
                ?><div class="alert alert-success"><?=Yii::t('app/feed', 'Selected reports has been deleted') ?></div> <?php
            } else {
                ?><div class="alert alert-danger"><?=Yii::t('app/feed', 'Some of selected reports could not be deleted!') ?></div> <?php
            }
        }
    }?>
    <p>
        <?= Html::a(Yii::t('app/feed', 'Create Report'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?=Html::beginForm(['feed/index'],'post');?>
    <?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "{summary}\n{pager}\n{items}\n{pager}",
        'rowOptions' => function ($model) {
            if (strtotime(date('Y-m-d\TH:i:s')) > strtotime($model->endtime))
            {
                return ['style' => 'background-color:#FFCCCC'];
            }
            elseif (strtotime(date('Y-m-d\TH:i:s')) < strtotime($model->starttime))
            {
                return ['style' => 'background-color:#CCCCFF'];
            }
        },
        'columns' => [
            ['class' => 'yii\grid\CheckboxColumn'],
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            //'incident_id',
            [
                'attribute' => 'authorFilterInput',
                'value' => 'author.login'
            ],
            'description',
            'created_at',
            // 'updated_at',
            // 'incident',
            // 'incidents',
            // 'location',
            // 'polyline',
            'starttime',
            'endtime',
            'street',
            'type',
            'subtype',
            'direction',
            // 'author_id',
            // 'reference',
            // 'source',
            // 'location_description',
            // 'name',
            // 'parent_event',
            // 'schedule',
            // 'short_description',
            // 'url:url',
            // 'active',
            // 'mail_send',
            'comment',

            ['class' => 'yii\grid\ActionColumn', 'template' => '{view}{delete}'],
            [
                'label' => 'Live Map',
                'format' => 'raw',
                'value' => function($model){
                    $polyline = explode(' ', $model->polyline);
                    if (count($polyline) < 2 || count($polyline) % 2 != 0)
                    {
                        // Invalid polyline!
                        return 'No link';
                    }

                    $lat = $polyline[0];
                    $lon = $polyline[1];
                    return Html::a(
                        Yii::t('app/feed', 'Go to Live!'),
                        "https://www.waze.com/en/livemap?zoom=17&lat=" . $lat . "&lon=". $lon,
                            [
                                'class' => 'btn btn-info',
                                'target' => '_blank'
                            ]
                    );
                }
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
    <div class="input-group">
        <span class="input-group-btn">
            <?= Html::submitButton(Yii::t('app/feed', 'Execute'), ['class' => 'btn btn-warning',]);?>
        </span>
        <?=Html::dropDownList('action','',
            [
                '' => Yii::t('app/feed', 'Actions with selected'),
                'r' => Yii::t('app/feed', 'Remove')
            ],
            ['class' => 'form-control'])?>

    </div>
    <?= Html::endForm();?>
</div>
