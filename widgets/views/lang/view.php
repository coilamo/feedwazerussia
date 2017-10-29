<?php
use yii\bootstrap\ButtonDropdown;
use yii\helpers\Url;


foreach ($langs as $lang)
{
    $items[] = [
        'label' => $lang->name,
        'url' => Url::current(['lang_id' => $lang->url])
        ];
}

echo ButtonDropdown::widget([
    'label' => Yii::t('app', 'Language'),
    'options' => [
        'class' => 'btn btn-link',
    ],
    'dropdown' => [
        'items' => $items,
    ]
]);

