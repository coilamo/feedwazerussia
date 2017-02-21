<?php 
session_start();	
include("config.php");	
include("db-function.php");
include("functions.php");
$user = getUserParams();


$last_id = mysql_getcell("SELECT MAX(id) FROM feed");
if(!isset($_POST["polyline"])) { 
	if($user) echo "Здравствуйте, " . $user['user_login'];
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Feed Waze Russia</title>
    <link rel="stylesheet" href="style.css?v=008" type="text/css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/build/jquery.datetimepicker.min.css">

    <style type="text/css">
        p.caption {
            width: 1024px;
        }
        .smallmap {
            border: 1px solid #CCCCCC;
	    height: 500px;
	 width: 1024px;
	}
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/openlayers/2.12/lib/OpenLayers.js"></script>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.2.4/jquery.datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
    <script type="text/javascript">
	
		
        var map, drawControls;
        var fromProjection = new OpenLayers.Projection("EPSG:4326"); // transform from WGS 1984
		var toProjection = new OpenLayers.Projection("EPSG:900913"); // to Spherical Mercator Projection
		var extent = new OpenLayers.Bounds(-1.32,51.71,-1.18,51.80).transform(fromProjection,toProjection);
        function init(){
			
           //map = new OpenLayers.Map("map", options);
           var bounds=new OpenLayers.Bounds();
           bounds.extend(new OpenLayers.LonLat(-179,85));
           bounds.extend(new OpenLayers.LonLat(179,	-85));
           
           map = new OpenLayers.Map('map',
            {controls:[
        	new OpenLayers.Control.Navigation(),
        	new OpenLayers.Control.PanZoomBar(),
        	new OpenLayers.Control.Scale(),
        	new OpenLayers.Control.MousePosition()],
        	maxResolution:.010986328125,
        	maxExtent:bounds,
        	numZoomLevels:19,
        	displayProjection:new OpenLayers.Projection("EPSG:4326"),
        	zoom:5}
           )

        var WazeLiveMapLayer = new OpenLayers.Layer.OSM(
          "Waze Livemap",
          ['https://tilesworld.waze.com/tiles/${z}/${x}/${y}.png'], 
          {zoomOffset: 0, numZoomLevels:19//,resolutions: [19567.8792375,9783.93961875,4891.969809375,2445.984904687,611.496226172,152.874056543,76.437028271,19.109257068,4.777314267,2.388657133]
              
          }
        );
        //var WazeLiveMapLayer = new OpenLayers.Layer.OSM.Mapnik("Base Layer",{displayOutsideMaxExtent:true,wrapDateLine:true});
        map.addLayer(WazeLiveMapLayer);
		
		
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
					this.selectedFeatures[0].geometry.components.forEach (function(item, i, arr){
						item_transf = item.transform(toProjection,fromProjection);
						if(polyline === '') {
							xy0 = item_transf;
						}
						polyline = polyline + item_transf.y.toFixed(6) + ' ' + item_transf.x.toFixed(6) + ' ';
					});
					/*
                    var point1 = new OpenLayers.Geometry.Point(xy0.x, xy0.y).transform(fromProjection,toProjection);
					var point2 = new OpenLayers.Geometry.Point(xy1.x, xy1.y).transform(fromProjection,toProjection);
					var line = new OpenLayers.Geometry.LineString([point1, point2]);       
					var distance = line.getGeodesicLength(new OpenLayers.Projection("EPSG:900913"));
					*/
                    //$('#length').html('Длина: ' + distance + ' м.');
                    $('input[name="length"]').val(40);
					$('textarea[name="polyline"]').val(polyline.trim());
					$('#form').show();
					$.get( "json.php?lat="+xy0.y+"&lon="+xy0.x, function( data ) {
					  $('#timezone_offset').val(data);
					});
					$('input[name="incident_id"]').val((xy0.y.toFixed(2)*100+xy0.x.toFixed(2)*100) + "FWR<?php echo $last_id + 1; ?>");
					
	
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
            console.log(WazeLiveMapLayer.getExtent);
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
					$.cookie("fwr_lon", map.getCenter().transform(toProjection,fromProjection).lon, { expires : 1000 });
					$.cookie("fwr_lat", map.getCenter().transform(toProjection,fromProjection).lat, { expires : 1000 });
					$.cookie("fwr_zoom", map.getZoom(), { expires : 1000 });

				}
			});
            
            for(var key in drawControls) {
                map.addControl(drawControls[key]);
            }
            var map_lon = (typeof $.cookie("fwr_lon") !== 'undefined') ?  $.cookie("fwr_lon") : 37.61;
            var map_lat = (typeof $.cookie("fwr_lat") !== 'undefined') ?  $.cookie("fwr_lat") : 55.76;
            var map_zoom = (typeof $.cookie("fwr_zoom") !== 'undefined') ?  $.cookie("fwr_zoom") : 7;
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
        
    </script>
  </head>
  <body onload="init()">
	<div id="msg"></div>
    <div id="map" class="smallmap"></div>
        <ul id="controlToggle">
        <li>
            <input type="radio" name="type" value="none" id="noneToggle"
                   onclick="toggleControl(this);" checked="checked" />
            <label for="noneToggle"><i class="fa fa-hand-paper-o" aria-hidden="true" title="Перемещать карту"></i></label>
        </li>
        <li>
            <input type="radio" name="type" value="line" id="lineToggle"
                   onclick="toggleControl(this);" />
            <label for="lineToggle"><i class="fa fa-map-pin" aria-hidden="true" title="Установите точку события, а вторым кликом определите направление"></i></label>
        </li>
        <li>
            <input type="radio" name="type" value="select" id="selectToggle"
                   onclick="toggleControl(this);" />
            <label for="selectToggle"><i class="fa fa-mouse-pointer" aria-hidden="true"></i></label>
            <ul style="display: none;">
                <li>
                    <input id="box" type="checkbox" checked="checked"
                           name="box" onchange="update()" />
                    <label for="box">select features in a box</label>
                </li>
                <li>
                    <input id="clickout" type="checkbox"
                           name="clickout" onchange="update()" />
                    <label for="clickout">click out to unselect features</label>
                </li>
            </ul>
        </li>
        <li><a id="location"><i class="fa fa-compass" aria-hidden="true" title="Найти моё местоположение"></i></a></li>
    </ul>
    <form id="form" name="alert" method="POST">
		
		
		<input type="hidden" name="timezone_offset" id="timezone_offset" value="+03:00">
		<input type="hidden" name="incident_id" value="">
		<input type="hidden" name="name" value="Russian community">
		<input type="hidden" name="length" value="0">
		
		
		<p><textarea name="polyline" readonly="readonly">test</textarea><span id="length"></span></p>
		<p><select name="direction" required>
			<option value>Выберите направление</option>
			<option value="ONE_DIRECTION">В одну сторону</option>
			<option value="BOTH_DIRECTIONS">В обе стороны</option>
		<select>
		</p>
		<p><select class="type" name="type" required>
				<option value="">Выберите тип события</option>
				<option value="ACCIDENT">Авария</option>
				<option value="CONSTRUCTION">Ремонт</option>
				<option value="HAZARD">Опасность</option>
				<option value="ROAD_CLOSED">Перекрытие</option>
			</select>

			<select style="display:none" class="subtype" name="subtype">
			</select>
		</p>
		<p><input type="text" id="starttime" name="starttime" placeholder="Дата начала" required> &mdash; <input type="text" id="endtime" name="endtime" placeholder="Дата окончания" required></p>
		<p><textarea name="description" placeholder="Описание" required></textarea></p>
		<p><input class="w330" type="text" name="street" placeholder="Улица" required/><input id="nostreet" type="checkbox"> Без названия</p>
		<?php if ($user) { ?>
			<p><input class="w330" type="text" name="author" value="<?php echo $user['user_login'];?>" readonly="readonly"/></p>
		<?php } else { ?>
			<p><input class="w330" type="text" name="author" placeholder="Ваш ник" required/></p>
		<?php } ?>
		<p><input class="w330" type="submit" value="Отправить" /></p>
    </form>
    <script type="text/javascript">
		var ACCIDENT = '<option value="ACCIDENT_MINOR">Мелкая авария</option><option value="ACCIDENT_MAJOR">Крупная авария</option>';
		var HAZARD = '<option value="HAZARD_ON_ROAD">Опасность на дороге</option><option value="HAZARD_ON_ROAD_CAR_STOPPED">Автомобиль остановился на дороге</option><option value="HAZARD_ON_ROAD_CONSTRUCTION">Ремонт</option><option value="HAZARD_ON_ROAD_OBJECT">Препятсвие</option><option value="HAZARD_ON_ROAD_POT_HOLE">Яма</option><option value="HAZARD_ON_ROAD_ROAD_KILL">Сбитое животное</option><option value="HAZARD_ON_SHOULDER">Опасность на обочине</option><option value="HAZARD_ON_SHOULDER_ANIMALS">Животное на обочине</option><option value="HAZARD_ON_SHOULDER_CAR_STOPPED">Стоит машина на обочине</option><option value="HAZARD_WEATHER">Плохая погода</option>';
		var ROAD_CLOSED = '<option value="ROAD_CLOSED_CONSTRUCTION">Ремонт</option><option value="ROAD_CLOSED_EVENT">Мероприятие</option><option value="ROAD_CLOSED_HAZARD">Опасность</option>';
		$('select.type').change(function(){
			$('select.subtype').show();
			if($(this).val() === 'HAZARD') {
				$('select.subtype').html(HAZARD);
			} else if($(this).val() === 'ACCIDENT') {
				$('select.subtype').html(ACCIDENT);
			} else if($(this).val() === 'ROAD_CLOSED') {
				$('select.subtype').html(ROAD_CLOSED);
			} else {
				$('select.subtype').hide();
			}

		});
$( function() {
	if(window.location.hash.substr(1)) {		
		$('#msg').html("Репорт id "+window.location.hash.substr(1).split('-')[1]+" отправлен на модерацию");
		window.location.hash = '';
	}
	
	$('#location').on("click", function() {
		navigator.geolocation.getCurrentPosition(
			function(position) {
				alert('Вы здесь: ' +
					position.coords.latitude + ", " + position.coords.longitude);
			}
		);
	});
	
	
	$('#nostreet').change(function() {
		if(this.checked) {
			$('input[name="street"]').val("No street");
			$('input[name="street"]').prop('readonly', "readonly");
		} else {
			$('input[name="street"]').val("");
			$('input[name="street"]').removeAttr('readonly');
		};
	});
});

$( function() {
	$( "#starttime" ).datetimepicker({ lang: "ru", format:"Y-m-d\\TH:i:s" });
	$( "#endtime" ).datetimepicker({ lang: "ru", format:"Y-m-d\\TH:i:s" });
} );


	</script>
  </body>
</html>

<?php 
} else {
	$data = $_POST;
	unset($_POST);
	$data["active"] = ($user['user_role'] == 2) ? 1 : 0;
	$data['starttime'] = $data['starttime'] . $data['timezone_offset'];
	$data['endtime'] = $data['endtime'] . $data['timezone_offset'];
	$data['creationtime'] = gmdate("Y-m-d\TH:i:s", time() + 3600*(str_replace('0','', $data['timezone_offset'])+date("I"))). $data['timezone_offset'];
	$data['updatetime'] = gmdate("Y-m-d\TH:i:s", time() + 3600*(str_replace('0','', $data['timezone_offset'])+date("I"))). $data['timezone_offset'];
	
	$error_text = "";
	
	if ((strlen(trim($data['author'])) < 1) || (strlen(trim($data['author'])) > 32)) {
		$error_text .= "Поле Имя пользователя должно содержать от 1 до 32 символов";
	}
	if ((strlen(trim($data['street'])) < 1) || (strlen(trim($data['street'])) > 100)) {
		$error_text .= "Поле Улица должно содержать от 1 до 100 символов";
	}
	if ((strlen(trim($data['starttime'])) < 19) || (strlen(trim($data['starttime'])) > 25)) {
		$error_text .= "Проверьте Дату начала";
	}
	if ((strlen(trim($data['endtime'])) < 19) || (strlen(trim($data['endtime'])) > 25)) {
		$error_text .= "Проверьте Дату окончания";
	}
	
	if (strlen(trim($data['timezone_offset'])) < 1) {
		$error_text .= "Ошибка данных";
	}
	
	if ((strlen(trim($data['description'])) < 3) || (strlen(trim($data['description'])) > 100)) {
		$error_text .= "Поле Описание должно содержать от 3 до 100 символов";
	}
	
	if ($data['length'] < 35) {
		$error_text .= "Длина вектора должна быть больше 35 метров.";
	}
	
	if(!empty($error_text)) {
			echo "Внимание, ошибки!<br/>" . $error_text . "<br/><a href=\"javascript:window.history.back();\">Вернитесь назад</a> и исправьте ошибки";
			exit();
	}

	unset($data['timezone_offset']);
	unset($data['length']);
	array_walk_recursive($data, "filter");
	if($last_id = mysql_write_row("feed", $data)) header("Location: /#success-". $last_id);
			
}
?>


