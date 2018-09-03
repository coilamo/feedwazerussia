<?php

use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Feed */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/feed', 'Reports'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/openlayers/2.13.1/theme/default/style.css', ['position' => View::POS_HEAD]);

$this->registerJsFile('https://code.jquery.com/jquery-1.12.4.min.js', ['position' => View::POS_HEAD]);
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/openlayers/2.13.1/lib/OpenLayers.js', ['position' => View::POS_HEAD]);
?>


<style>
.smallmap {
    width: 100%;
    height: 512px;
    border: 1px solid #ccc;
}
</style>

<div class="feed-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php if($deleteNotAllowed) { ?>
        <div class="alert alert-danger"><?=Yii::t('app/feed', 'You\'re not allowed to delete this feed!') ?></div>
    <?php } ?>
    <div id="map" class="smallmap"></div>

    <p>
        <?php // Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app/feed', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app/feed', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
        <?php
            $polyline = explode(' ', $model->polyline);
            if (!(count($polyline) < 2 || count($polyline) % 2 != 0))
            {
                $lat = $polyline[0];
                $lon = $polyline[1];
                echo Html::a(
                    Yii::t('app/feed', 'Go to Live!'),
                    "https://www.waze.com/en/livemap?zoom=17&lat=" . $lat . "&lon=". $lon,
                        [
                            'class' => 'btn btn-info',
                            'target' => '_blank'
                        ]
                );
            }
        ?>
        <?= Html::a(Yii::t('app/feed', 'Clone'), ['clone', 'id' => $model->id], [
            'class' => 'btn btn-warning',
            'data' => [
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::beginForm(['extend', 'id' => $model->id], 'post', [ 'class' => "form-inline"]); ?>
    <div class="form-group">
        <label for="days"><?= Yii::t('app/feed', 'Days to extend:'); ?></label>
        <?= Html::input('number', 'days', null, ['class' => 'form-control']); ?>
    </div>
        <?= Html::submitButton(Yii::t('app/feed', 'Extend'), [
            'class' => 'btn btn-success',
        ]); ?>
        <?= Html::endForm(); ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'incident_id',
            'description',
            'created_at',
            'updated_at',
            'incident',
            'incidents',
            'location',
            'polyline',
            'starttime',
            'endtime',
            'street',
            'type',
            'direction',
            'author_id',
            'reference',
            'source',
            'location_description',
            'name',
            'parent_event',
            'schedule',
            'short_description',
            'subtype',
            'url:url',
            'active',
            'mail_send',
            'comment',
        ],
    ]) ?>

    <script>
    var map, drawControls;
    var fromProjection = new OpenLayers.Projection("EPSG:4326"); // transform from WGS 1984
            var toProjection = new OpenLayers.Projection("EPSG:900913"); // to Spherical Mercator Projection
            var extent = new OpenLayers.Bounds(-1.32,51.71,-1.18,51.80).transform(fromProjection,toProjection);
    function init(){
        var bounds = new OpenLayers.Bounds();
        bounds.extend(new OpenLayers.LonLat(-179, 85));
        bounds.extend(new OpenLayers.LonLat(179, -85));

        map = new OpenLayers.Map('map',
        {
            controls:[
                new OpenLayers.Control.Navigation(),
                new OpenLayers.Control.PanZoomBar(),
                new OpenLayers.Control.Scale(),
                new OpenLayers.Control.MousePosition()],
            maxResolution:.010986328125,
            maxExtent:bounds,
            numZoomLevels:19,
            displayProjection:new OpenLayers.Projection("EPSG:4326"),
            zoom:5}
        );

        var WazeLiveMapLayer = new OpenLayers.Layer.OSM(
            "Waze Livemap",
            ['https://tilesworld.waze.com/tiles/${z}/${x}/${y}.png'], 
            {
                zoomOffset: 0,
                numZoomLevels:19
            }
        );

        var markers = new OpenLayers.Layer.Markers( "Markers" );
        var size = new OpenLayers.Size(21,25);
        var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
        var icon = new OpenLayers.Icon('http://rebelseed.com/wp-content/plugins/superstorefinder-wp/images/icons/youarehere.png',size,offset);
        
        <?php
            $polyline = explode(' ', $model->polyline);
            if (count($polyline) >1)
            {
                $lat = $polyline[0];
                $lon = $polyline[1];
                echo 'var lat = ' . $lat .';';
                echo 'var lon = ' . $lon .';';
            }
            else
            {
                echo 'var lat = 0, lon = 0;';
            }
                
        ?>
        if (lat !== 0 && lon !== 0)
        {
            markers.addMarker(new OpenLayers.Marker(new OpenLayers.LonLat(lon, lat).transform(fromProjection,toProjection),icon));
        }
        map.addLayers([WazeLiveMapLayer, markers]);
        map.addControl(new OpenLayers.Control.LayerSwitcher());
        if (lat !== 0 && lon !== 0)
        {
            map.setCenter(new OpenLayers.LonLat(lon, lat).transform(fromProjection,toProjection), 14); // 0=relative zoom level 
        }
    }
    $(function() {
            init();
    });
    </script>
</div>
