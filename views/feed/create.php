<?php

use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Feed */

$this->title = Yii::t('app', 'Create Report');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reports'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/openlayers/2.13.1/theme/default/style.css', ['position' => View::POS_HEAD]);
$this->registerCssFile('https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', ['position' => View::POS_HEAD]);

$this->registerJsFile('https://code.jquery.com/jquery-1.12.4.min.js', ['position' => View::POS_HEAD]);
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.4/js.cookie.min.js', ['position' => View::POS_HEAD]);
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/openlayers/2.13.1/lib/OpenLayers.js', ['position' => View::POS_HEAD]);

?>

<style>
.smallmap {
    width: 100%;
    height: 512px;
    border: 1px solid #ccc;
}
ul, li {
	padding:0;
	margin:0;
}

li {
    list-style-type: none;
    float: left;
    margin-right: 12px;
}

ul {
	overflow: hidden;
}
#controlToggle input[type="radio"]{
	display:none;
}

#controlToggle label {
	display: block;
	width: 24px;
	height:24px;
	line-height: 24px;
	text-align: center;
	border: 1px solid white;
}

#controlToggle input:checked + label {
	border: 1px solid red;
}
</style>
<div class="feed-create">

    <h1><?= Html::encode($this->title) ?></h1>
    <div id="map" class="smallmap"></div>
    <ul id="controlToggle">
        <li>
            <input type="radio" name="type" value="none" id="noneToggle" onclick="toggleControl(this);" checked="checked" />
            <label for="noneToggle"><i class="fa fa-hand-paper-o" aria-hidden="true" title="Перемещать карту"></i></label>
        </li>
        <li>
            <input type="radio" name="type" value="line" id="lineToggle" onclick="toggleControl(this);" />
            <label for="lineToggle"><i class="fa fa-map-pin" aria-hidden="true" title="Установите точку события, а вторым кликом определите направление"></i></label>
        </li>
        <li>
            <input type="radio" name="type" value="select" id="selectToggle" onclick="toggleControl(this);" />
            <label for="selectToggle"><i class="fa fa-mouse-pointer" aria-hidden="true"></i></label>
            <ul style="display: none;">
                <li>
                    <input id="box" type="checkbox" checked="checked" name="box" onchange="update()" />
                    <label for="box">select features in a box</label>
                </li>
                <li>
                    <input id="clickout" type="checkbox" name="clickout" onchange="update()" />
                    <label for="clickout">click out to unselect features</label>
                </li>
            </ul>
        </li>
        <li><a id="location"><i class="fa fa-compass" aria-hidden="true" title="Найти моё местоположение"></i></a></li>
    </ul>
    
    <?= $this->render('_form', [
        'model' => $model,
        'hide' => $hide
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

        // allow testing of specific renderers via "?renderer=Canvas", etc
        var renderer = OpenLayers.Util.getParameters(window.location.href).renderer;
        renderer = (renderer) ? [renderer] : OpenLayers.Layer.Vector.prototype.renderers;
        var vectors = new OpenLayers.Layer.Vector("Vector Layer", {
            renderers: renderer
        });
        vectors.events.on({
            'featureselected': function(feature) {
                // TODO: Feature selected
                var polyline = "";
                var xy0;
                this.selectedFeatures[0].geometry.components.forEach (function(item, i, arr){
                        item_transf = item.transform(toProjection,fromProjection);
                        if(polyline === '') {
                                xy0 = item_transf;
                        }
                        polyline = polyline + item_transf.y.toFixed(6) + ' ' + item_transf.x.toFixed(6) + ' ';
                });
                $('#feed-polyline').val(polyline.trim());
                $.get( "<?= Url::to(['feed/getstreet']) ?>&lat="+xy0.y+"&lon="+xy0.x, function( data ) {
                        if( data == "" ) {
                                $('#feed-street').val("");
                        }else{						
                                $('#feed-street').val(data);
                        }
                });
                $('#feed-form').show();

                var size = new OpenLayers.Size(24,24);
                var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
                var icon = new OpenLayers.Icon('https://individual.icons-land.com/IconsPreview/MapMarkers/PNG/Centered/24x24/MapMarker_Flag4_Right_Pink.png', size, offset);
                markers.addMarker(new OpenLayers.Marker(new OpenLayers.LonLat(xy0.x,xy0.y).transform(fromProjection,toProjection),icon));

            },
            'featureunselected': function(feature) {
                //document.getElementById('counter').innerHTML = this.selectedFeatures.length;
            }
        });

        map.addLayers([WazeLiveMapLayer, vectors, markers]);
        map.addControl(new OpenLayers.Control.LayerSwitcher());

        drawControls = {
            point: new OpenLayers.Control.DrawFeature(
                vectors, OpenLayers.Handler.Point
            ),
            line: new OpenLayers.Control.DrawFeature(
                vectors, OpenLayers.Handler.Path,
                {
                    eventListeners: { "featureadded": function(feature) { 
                                    $('label[for="selectToggle"]').click();
                            }
                    },
                    handlerOptions: {
                            //maxVertices: 2,
                            //single: true,
                            freehand: false,
                    }
                }
            ),
            polygon: new OpenLayers.Control.DrawFeature(
                vectors, OpenLayers.Handler.Polygon
            ),
            select: new OpenLayers.Control.SelectFeature(
                vectors,
                {
                    clickout: false, toggle: true,
                    multiple: false, hover: false,
                    toggleKey: "ctrlKey", // ctrl key removes from selection
                    multipleKey: "shiftKey", // shift key adds to selection
                    box: true
                }
            ),
            selecthover: new OpenLayers.Control.SelectFeature(
                vectors,
                {
                    multiple: false, hover: true,
                    toggleKey: "ctrlKey", // ctrl key removes from selection
                    multipleKey: "shiftKey" // shift key adds to selection
                }
            )
        };

        map.events.on({
            "moveend":function(){
                Cookies.set("fwr_lon", map.getCenter().transform(toProjection,fromProjection).lon, { expires : 1000 });
                Cookies.set("fwr_lat", map.getCenter().transform(toProjection,fromProjection).lat, { expires : 1000 });
                Cookies.set("fwr_zoom", map.getZoom(), { expires : 1000 });
            }
        });

        for(var key in drawControls) {
            map.addControl(drawControls[key]);
        }
        var map_lon = (Cookies.get("fwr_lon") === undefined) ?  37.61 : Cookies.get("fwr_lon");
        var map_lat = (Cookies.get("fwr_lat") === undefined) ?  55.76 : Cookies.get("fwr_lat");
        var map_zoom = (Cookies.get("fwr_zoom") === undefined) ?  7 : Cookies.get("fwr_zoom");
        map.setCenter(new OpenLayers.LonLat(map_lon, map_lat).transform(fromProjection,toProjection), map_zoom); // 0=relative zoom level 

    }
    function toggleControl(element) {
        for(key in drawControls) {
            var control = drawControls[key];
            if(element.value == key && element.checked) {
                control.activate();
            } else {
                control.deactivate();
            }
        }
    }
    function update() {
        var clickout = document.getElementById("clickout").checked;
        if(clickout != drawControls.select.clickout) {
            drawControls.select.clickout = clickout;
        }
        var box = document.getElementById("box").checked;
        if(box != drawControls.select.box) {
            drawControls.select.box = box;
            if(drawControls.select.active) {
                drawControls.select.deactivate();
                drawControls.select.activate();
            }
        }
    }
    $(function() {
            init();
    });
    
    var POLICE = '<option value="POLICE_VISIBLE">Видимая полиция</option><option value="POLICE_HIDING">Скрытая засада полиции</option>';
    var JAM = '<option value="JAM_LIGHT_TRAFFIC">Небольшая пробка</option><option value="JAM_MODERATE_TRAFFIC">Средняя пробка</option><option value="JAM_HEAVY_TRAFFIC">Стоим в пробке</option><option value="JAM_STAND_STILL_TRAFFIC">Полный тупик</option>';
    var ACCIDENT = '<option value="ACCIDENT_MINOR">Мелкая авария</option><option value="ACCIDENT_MAJOR">Крупная авария</option>';
    var HAZARD = '<option value="HAZARD_ON_ROAD">Опасность на дороге</option><option value="HAZARD_ON_ROAD_CAR_STOPPED">Автомобиль остановился на дороге</option><option value="HAZARD_ON_ROAD_CONSTRUCTION">Ремонт</option><option value="HAZARD_ON_ROAD_OBJECT">Препятсвие</option><option value="HAZARD_ON_ROAD_POT_HOLE">Яма</option><option value="HAZARD_ON_ROAD_ROAD_KILL">Сбитое животное</option><option value="HAZARD_ON_SHOULDER">Опасность на обочине</option><option value="HAZARD_ON_SHOULDER_ANIMALS">Животное на обочине</option><option value="HAZARD_ON_SHOULDER_CAR_STOPPED">Стоит машина на обочине</option><option value="HAZARD_WEATHER">Плохая погода</option><option value="HAZARD_ON_SHOULDER_MISSING_SIGN">Отсутствует знак</option><option value="HAZARD_WEATHER_FOG">Туман</option><option value="HAZARD_WEATHER_HAIL">Град</option><option value="HAZARD_WEATHER_HEAVY_RAIN">Ливень</option><option value="HAZARD_WEATHER_HEAVY_SNOW">Сильный снегопад</option><option value="HAZARD_WEATHER_FLOOD">Наводнение</option><option value="HAZARD_WEATHER_MONSOON">Муссон</option><option value="HAZARD_WEATHER_TORNADO">Торнадо</option><option value="HAZARD_WEATHER_HEAT_WAVE">Сильная жара</option><option value="HAZARD_WEATHER_HURRICANE">Ураган</option><option value="HAZARD_WEATHER_FREEZING_RAIN">Ледяной дождь</option><option value="HAZARD_ON_ROAD_LANE_CLOSED">Закрыта полоса</option><option value="HAZARD_ON_ROAD_OIL">Пролито масло</option><option value="HAZARD_ON_ROAD_ICE">Гололёд</option>';
    var ROAD_CLOSED = '<option value="ROAD_CLOSED_CONSTRUCTION">Ремонт</option><option value="ROAD_CLOSED_EVENT">Мероприятие</option><option value="ROAD_CLOSED_HAZARD">Опасность</option>';
    $('#feed-type').change(function(){
        $('#feed-subtype').show();
        if($(this).val() === 'HAZARD') {
                $('#feed-subtype').html(HAZARD);
        } else if($(this).val() === 'POLICE') {
                $('#feed-subtype').html(POLICE);
        } else if($(this).val() === 'JAM') {
                $('#feed-subtype').html(JAM);
        } else if($(this).val() === 'ACCIDENT') {
                $('#feed-subtype').html(ACCIDENT);
        } else if($(this).val() === 'ROAD_CLOSED') {
                $('#feed-subtype').html(ROAD_CLOSED);
        } else {
                $('#feed-subtype').hide();
		$('#feed-subtype').html('');
        }
    });
    </script>
</div>
