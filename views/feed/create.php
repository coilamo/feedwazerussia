<?php

use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Feed */

$this->title = Yii::t('app/feed', 'Create Report');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reports'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/openlayers/2.13.1/theme/default/style.css', ['position' => View::POS_HEAD]);
$this->registerCssFile('https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', ['position' => View::POS_HEAD]);

$this->registerJsFile('https://code.jquery.com/jquery-1.12.4.min.js', ['position' => View::POS_HEAD]);
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.4/js.cookie.min.js', ['position' => View::POS_HEAD]);
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/openlayers/2.13.1/lib/OpenLayers.js', ['position' => View::POS_HEAD]);
$this->registerJsFile('https://maps.google.com/maps/api/js?v=3&sensor=false&key=' . \Yii::$app->params['googleKey'], ['position' => View::POS_HEAD]);

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
    <p>
        Адрес:<input type="text" size="60" name="int_address" id="int_address" />
        <input type="submit" value="Go!" onclick="showAddress($('#int_address').val());"/>
    </p>
    <div id="map" class="smallmap"></div>
    <ul id="controlToggle">
        <li>
            <input type="radio" name="type" value="none" id="noneToggle" onclick="toggleControl(this);" checked="checked" />
            <label for="noneToggle"><i class="fa fa-hand-paper-o" aria-hidden="true" title="<?= Yii::t('app/feed', 'Move the map'); ?>"></i></label>
        </li>
        <li>
            <input type="radio" name="type" value="point" id="pointToggle" onclick="toggleControl(this);" />
            <label for="pointToggle"><i class="fa fa-map-marker" aria-hidden="true" title="<?= Yii::t('app/feed', 'Select the report point'); ?>"></i></label>
        </li>
        <li>
            <input type="radio" name="type" value="line" id="lineToggle" onclick="toggleControl(this);" />
            <label for="lineToggle"><i class="fa fa-map-pin" aria-hidden="true" title="<?= Yii::t('app/feed', 'Select the report point and direction by 2nd click'); ?>"></i></label>
        </li>
        <li>
            <input type="radio" name="type" value="select" id="selectToggle" onclick="toggleControl(this);" />
            <label for="selectToggle"><i class="fa fa-mouse-pointer" aria-hidden="true"></i></label>
            <ul style="display: none;">
                <li>
                    <input id="box" type="checkbox" checked="checked" name="box" onchange="update()" />
                    <label for="box"><?= Yii::t('app/feed', 'Select features in a box'); ?></label>
                </li>
                <li>
                    <input id="clickout" type="checkbox" name="clickout" onchange="update()" />
                    <label for="clickout"><?= Yii::t('app/feed', 'Click out to unselect features'); ?></label>
                </li>
            </ul>
        </li>
        <li><a id="location"><i class="fa fa-compass" aria-hidden="true" title="<?= Yii::t('app/feed', 'Locate my position'); ?>" onclick="locateMyPosition();"></i></a></li>
    </ul>
    
    <?= $this->render('_form', [
        'model' => $model,
        'hide' => $hide,
        'allowedTypes' => $allowedTypes,
    ]) ?>
    
    <script>
    var map, drawControls;
    var geocoder;
    var fromProjection = new OpenLayers.Projection("EPSG:4326"); // transform from WGS 1984
            var toProjection = new OpenLayers.Projection("EPSG:900913"); // to Spherical Mercator Projection
            var extent = new OpenLayers.Bounds(-1.32,51.71,-1.18,51.80).transform(fromProjection,toProjection);
    function init(){
        var bounds = new OpenLayers.Bounds();
        bounds.extend(new OpenLayers.LonLat(-179, 85));
        bounds.extend(new OpenLayers.LonLat(179, -85));

        geocoder = new google.maps.Geocoder();
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
                var polyline = "";
                var xy0;
                if (this.selectedFeatures[0].geometry.components === undefined) {
                    // This is point
                    item_transf = this.selectedFeatures[0].geometry.transform(toProjection,fromProjection);
                    if(polyline === '') {
                            xy0 = item_transf;
                    }
                    polyline = polyline + item_transf.y.toFixed(6) + ' ' + item_transf.x.toFixed(6) + ' ';
                    //$('#feed-direction').val("BOTH_DIRECTIONS");
                    //$('#feed-direction').hide();
                } else {
                    // TODO: Feature selected
                    this.selectedFeatures[0].geometry.components.forEach (function(item, i, arr){
                        item_transf = item.transform(toProjection,fromProjection);
                        if(polyline === '') {
                                xy0 = item_transf;
                        }
                        polyline = polyline + item_transf.y.toFixed(6) + ' ' + item_transf.x.toFixed(6) + ' ';
                    });
                    //$('#feed-direction').val("");
                    //$('#feed-direction').show();
                }
                $('#feed-polyline').val(polyline.trim());
                $.get( "<?= Url::to(['feed/getstreet']) ?>?lat="+xy0.y+"&lon="+xy0.x, function( data ) {
                        if( data === "" ) {
                                $('#feed-street').val("");
                        }else{
                                $('#feed-street').val(data);
                        }
                });
                $('#feed-form').show();

                showMarker(markers, xy0.x,xy0.y);

            },
            'featureunselected': function(feature) {
                //document.getElementById('counter').innerHTML = this.selectedFeatures.length;
            }
        });

        map.addLayers([WazeLiveMapLayer, vectors, markers,
            new OpenLayers.Layer.Google(
                "Google Physical",
                {type: google.maps.MapTypeId.TERRAIN}
            ),
            new OpenLayers.Layer.Google(
                "Google Hybrid",
                {type: google.maps.MapTypeId.HYBRID, numZoomLevels: 20}
            ),
            new OpenLayers.Layer.Google(
                "Google Satellite",
                {type: google.maps.MapTypeId.SATELLITE, numZoomLevels: 22}
            )]);
        map.addControl(new OpenLayers.Control.LayerSwitcher());

        drawControls = {
            point: new OpenLayers.Control.DrawFeature(
                vectors, OpenLayers.Handler.Point,
                {
                    eventListeners: { "featureadded": function(feature) { 
                                    $('label[for="selectToggle"]').click();
                            }
                    },
                    handlerOptions: {
                            //maxVertices: 2,
                            //single: true,
                            freehand: false
                    }
                }
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
                            freehand: false
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

        <?php
        if ($model->type != null && $model->subtype != null) {
        ?>
        toggleFeedType();
        $('#feed-subtype').val('<?= $model->subtype; ?>').change();
        <?php
        }

        if ($model->polyline != null) {
            $coords = explode(" ", $model->polyline);
            if (count($coords) > 1) {
                echo 'showMarker(markers, ' . $coords[count($coords) - 1] . ', ' . $coords[count($coords) - 2] . ');';
                echo 'map.setCenter(new OpenLayers.LonLat(' . $coords[count($coords) - 1] . ', ' . $coords[count($coords) - 2] . ').transform(fromProjection, toProjection), map.getZoom());';
            }
            if (count($coords) > 2 && count($coords) % 2 == 0) {
                echo 'var points = new OpenLayers.Geometry.LineString( [';
                for ($i = 0; $i < count($coords) / 2; $i++) {
                    echo 'new OpenLayers.Geometry.Point('.$coords[$i * 2 + 1] . ', ' . $coords[$i * 2].').transform(fromProjection,toProjection),';
                }
                ?>
                ]);
                var linefeature = new OpenLayers.Feature.Vector(points);
                vectors.addFeatures( [linefeature] );
        <?php
            }
        }
        ?>
    }

    function showMarker(markers, x, y) {
        var size = new OpenLayers.Size(24,24);
        var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
        var icon = new OpenLayers.Icon('https://individual.icons-land.com/IconsPreview/MapMarkers/PNG/Centered/24x24/MapMarker_Flag4_Right_Pink.png', size, offset);
        markers.addMarker(new OpenLayers.Marker(new OpenLayers.LonLat(x,y).transform(fromProjection,toProjection),icon));
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
    
    var POLICE = '<option value="POLICE_VISIBLE"><?= Yii::t('app/feed', 'Visible police'); ?></option><option value="POLICE_HIDING"><?= Yii::t('app/feed', 'Hidden police'); ?></option>';
    var JAM = '<option value="JAM_LIGHT_TRAFFIC"><?= Yii::t('app/feed', 'Light traffic'); ?></option><option value="JAM_MODERATE_TRAFFIC"><?= Yii::t('app/feed', 'Moderate traffic'); ?></option><option value="JAM_HEAVY_TRAFFIC"><?= Yii::t('app/feed', 'Heavy traffic'); ?></option><option value="JAM_STAND_STILL_TRAFFIC"><?= Yii::t('app/feed', 'Stand still traffic'); ?></option>';
    var ACCIDENT = '<option value="ACCIDENT_MINOR"><?= Yii::t('app/feed', 'Minor accident'); ?></option><option value="ACCIDENT_MAJOR"><?= Yii::t('app/feed', 'Major accident'); ?></option>';
    var HAZARD = '<option value="HAZARD_ON_ROAD"><?= Yii::t('app/feed', 'Hazard on road'); ?></option><option value="HAZARD_ON_ROAD_CAR_STOPPED"><?= Yii::t('app/feed', 'Car stopped on road'); ?></option><option value="HAZARD_ON_ROAD_CONSTRUCTION"><?= Yii::t('app/feed', 'Construction'); ?></option><option value="HAZARD_ON_ROAD_OBJECT"><?= Yii::t('app/feed', 'Object'); ?></option><option value="HAZARD_ON_ROAD_POT_HOLE"><?= Yii::t('app/feed', 'Pot hole'); ?></option><option value="HAZARD_ON_ROAD_ROAD_KILL"><?= Yii::t('app/feed', 'Road kill'); ?></option><option value="HAZARD_ON_SHOULDER"><?= Yii::t('app/feed', 'Hazard on shoulder'); ?></option><option value="HAZARD_ON_SHOULDER_ANIMALS"><?= Yii::t('app/feed', 'Animals on shoulder'); ?></option><option value="HAZARD_ON_SHOULDER_CAR_STOPPED"><?= Yii::t('app/feed', 'Car stopped on shoulder'); ?></option><option value="HAZARD_WEATHER"><?= Yii::t('app/feed', 'Hazard weather'); ?></option><option value="HAZARD_ON_SHOULDER_MISSING_SIGN"><?= Yii::t('app/feed', 'Missing sign'); ?></option><option value="HAZARD_WEATHER_FOG"><?= Yii::t('app/feed', 'Fog'); ?></option><option value="HAZARD_WEATHER_HAIL"><?= Yii::t('app/feed', 'Hail'); ?></option><option value="HAZARD_WEATHER_HEAVY_RAIN"><?= Yii::t('app/feed', 'Heavy rain'); ?></option><option value="HAZARD_WEATHER_HEAVY_SNOW"><?= Yii::t('app/feed', 'Heavy snow'); ?></option><option value="HAZARD_WEATHER_FLOOD"><?= Yii::t('app/feed', 'Flood'); ?></option><option value="HAZARD_WEATHER_MONSOON"><?= Yii::t('app/feed', 'Monson'); ?></option><option value="HAZARD_WEATHER_TORNADO"><?= Yii::t('app/feed', 'Tornado'); ?></option><option value="HAZARD_WEATHER_HEAT_WAVE"><?= Yii::t('app/feed', 'Heat wave'); ?></option><option value="HAZARD_WEATHER_HURRICANE"><?= Yii::t('app/feed', 'Hurricane'); ?></option><option value="HAZARD_WEATHER_FREEZING_RAIN"><?= Yii::t('app/feed', 'Freezing rain'); ?></option><option value="HAZARD_ON_ROAD_LANE_CLOSED"><?= Yii::t('app/feed', 'Lane closed'); ?></option><option value="HAZARD_ON_ROAD_OIL"><?= Yii::t('app/feed', 'Oil on road'); ?></option><option value="HAZARD_ON_ROAD_ICE"><?= Yii::t('app/feed', 'Ice'); ?></option><option value="HAZARD_ON_ROAD_TRAFFIC_LIGHT_FAULT"><?= Yii::t('app/feed', 'Traffic light fault'); ?></option>';
    var ROAD_CLOSED = '<option value="ROAD_CLOSED_CONSTRUCTION"><?= Yii::t('app/feed', 'Construction'); ?></option><option value="ROAD_CLOSED_EVENT"><?= Yii::t('app/feed', 'Event'); ?></option><option value="ROAD_CLOSED_HAZARD"><?= Yii::t('app/feed', 'Hazard'); ?></option>';
    $('#feed-type').change(toggleFeedType);

    function toggleFeedType() {
        $('#feed-subtype').show();
        if($('#feed-type').val() === 'HAZARD') {
            $('#feed-subtype').html(HAZARD);
        } else if($('#feed-type').val() === 'POLICE') {
            $('#feed-subtype').html(POLICE);
        } else if($('#feed-type').val() === 'JAM') {
            $('#feed-subtype').html(JAM);
        } else if($('#feed-type').val() === 'ACCIDENT') {
            $('#feed-subtype').html(ACCIDENT);
        } else if($('#feed-type').val() === 'ROAD_CLOSED') {
            $('#feed-subtype').html(ROAD_CLOSED);
        } else {
            $('#feed-subtype').hide();
            $('#feed-subtype').html('');
        }
    }
    
    function showAddress(addr) {
        if (geocoder) {
            geocoder.geocode( {
                address: addr,
                componentRestrictions: {
                    country: 'ru'
                  }
              },
              function(results, status) {
                if (status === 'OK') {
                    var lat = results[0].geometry.location.lat();
                    var lon = results[0].geometry.location.lng();
                    map.setCenter(new OpenLayers.LonLat(lon, lat).transform(fromProjection, toProjection), map.getZoom());
                }
              });
        }
    }
    
    function locateMyPosition() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition);
        }
    }
    
    function showPosition(position) {
        map.setCenter(new OpenLayers.LonLat(position.coords.longitude, position.coords.latitude).transform(fromProjection, toProjection), map.getZoom());
    }
    </script>
</div>
