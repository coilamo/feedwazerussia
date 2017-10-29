<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Feed */

$this->title = Yii::t('app/feed', 'Update {modelClass}: ', [
    'modelClass' => 'Feed',
]) . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reports'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app/feed', 'Update');
?>
<div class="feed-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
