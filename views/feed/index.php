<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\FeedSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Reports');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="feed-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', 'Create Report'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
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
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'incident_id',
            'description',
            'created_at',
            // 'updated_at',
            // 'incident',
            // 'incidents',
            // 'location',
            'polyline',
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
                        'Go to Live!',
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
<?php Pjax::end(); ?></div>
