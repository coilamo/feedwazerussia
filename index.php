<?php 
error_reporting(E_ALL);
$username = "flusspferd_feedw";
$password = "34831";
$hostname = "localhost";
$dbname = "flusspferd_feedw";

$mysqli = mysqli_connect($hostname, $username, $password, $dbname);
mysqli_set_charset($mysqli, "utf8");

include("db-function.php");	
$last_id = mysql_getcell("SELECT MAX(id) FROM feed");
if(!isset($_POST["polyline"])) { 
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>OpenLayers Image Layer Example</title>
    <link rel="stylesheet" href="style.css?v=001" type="text/css">
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
    <script src="http://dev.openlayers.org/OpenLayers.js"></script>
    <script  src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.2.4/jquery.datetimepicker.min.js"></script>
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
          ['http://tilesworld.waze.com/tiles/${z}/${x}/${y}.png'], 
          {zoomOffset: 0, numZoomLevels:19//,resolutions: [19567.8792375,9783.93961875,4891.969809375,2445.984904687,611.496226172,152.874056543,76.437028271,19.109257068,4.777314267,2.388657133]
              
          }
        );
        //var WazeLiveMapLayer = new OpenLayers.Layer.OSM.Mapnik("Base Layer",{displayOutsideMaxExtent:true,wrapDateLine:true});
        map.addLayer(WazeLiveMapLayer);
       
        
            // allow testing of specific renderers via "?renderer=Canvas", etc
            var renderer = OpenLayers.Util.getParameters(window.location.href).renderer;
            renderer = (renderer) ? [renderer] : OpenLayers.Layer.Vector.prototype.renderers;

            var vectors = new OpenLayers.Layer.Vector("Vector Layer", {
                renderers: renderer
            });
            vectors.events.on({
                'featureselected': function(feature) {
                    var xy0 = this.selectedFeatures[0].geometry.components[0].transform(toProjection,fromProjection);
                    var xy1 = this.selectedFeatures[0].geometry.components[1].transform(toProjection,fromProjection);
					
					$('input#start').val(xy0.y.toFixed(6)+' '+xy0.x.toFixed(6));
					$('input#end').val(xy1.y.toFixed(6)+' '+xy1.x.toFixed(6));
					$('input[name="polyline"]').val(xy0.y.toFixed(6) + ' ' + xy0.x.toFixed(6) + ' ' + xy1.y.toFixed(6) + ' ' + xy1.x.toFixed(6));
					$('#form').show();
					$.get( "json.php?lat="+xy0.y+"&lon="+xy0.x, function( data ) {
					  $('#timezone_offset').val(data);
					});
					$('input[name="incident_id"]').val((xy0.y.toFixed(2)*100+xy0.x.toFixed(2)*100) + "FWR<?php echo $last_id; ?>");
                },
                'featureunselected': function(feature) {
                    //document.getElementById('counter').innerHTML = this.selectedFeatures.length;
                }
            });

            map.addLayers([WazeLiveMapLayer, vectors]);
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
									maxVertices: 2,
									single: true,
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
            
            for(var key in drawControls) {
                map.addControl(drawControls[key]);
            }
            map.setCenter(new OpenLayers.LonLat(37.09,55.66).transform(fromProjection,toProjection), 14); // 0=relative zoom level 
        
        
        
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
    </ul>
    <form id="form" name="alert" method="POST">
		
		
		<input type="hidden" name="timezone_offset" id="timezone_offset" value="+03:00">
		<input type="hidden" name="incident_id" value="">
		<input type="hidden" name="creationtime" value="<?php echo date("c"); ?>">
		<input type="hidden" name="updatetime" value="<?php echo date("c"); ?>">
		<input type="hidden" name="direction" value="ONE_DIRECTION">
		
		
		
		
		<p><input type="text" id="start" readonly placeholder="Координаты точки старта"> &mdash; <input type="text" id="end" readonly>
		<input type="hidden" name="polyline" value="2">
		</p>
		<p><select class="type" name="type">
				<option value="">Выберите тип события</option>
				<option value="ACCIDENT">ACCIDENT</option>
				<option value="CONSTRUCTION">CONSTRUCTION</option>
				<option value="HAZARD">HAZARD</option>
				<option value="ROAD_CLOSED">ROAD_CLOSED</option>
			</select>

			<select style="display:none" class="subtype" name="subtype">
			</select>
		</p>
		<p><input type="text" id="starttime" name="starttime" placeholder="Дата начала"> &mdash; <input type="text" id="endtime" name="endtime" placeholder="Дата окончания"></p>
		<p><textarea name="description" placeholder="Описание"></textarea></p>
		<p><input class="w330" type="text" name="street" placeholder="Улица" /></p>
		<p><input class="w330" type="text" name="author" placeholder="Ваш ник" /></p>
		<p><input class="w330" type="submit" value="Отправить" /></p>
    </form>
    <script type="text/javascript">
		var ACCIDENT = '<option value="ACCIDENT_MINOR">ACCIDENT_MINOR</option><option value="ACCIDENT_MAJOR">ACCIDENT_MAJOR</option>';
		var HAZARD = '<option value="HAZARD_ON_ROAD">HAZARD_ON_ROAD</option><option value="HAZARD_ON_ROAD_CAR_STOPPED">HAZARD_ON_ROAD_CAR_STOPPED</option><option value="HAZARD_ON_ROAD_CONSTRUCTION">HAZARD_ON_ROAD_CONSTRUCTION</option><option value="HAZARD_ON_ROAD_OBJECT">HAZARD_ON_ROAD_OBJECT</option><option value="HAZARD_ON_ROAD_POT_HOLE">HAZARD_ON_ROAD_POT_HOLE</option><option value="HAZARD_ON_ROAD_ROAD_KILL">HAZARD_ON_ROAD_ROAD_KILL</option><option value="HAZARD_ON_SHOULDER">HAZARD_ON_SHOULDER</option><option value="HAZARD_ON_SHOULDER_ANIMALS">HAZARD_ON_SHOULDER_ANIMALS</option><option value="HAZARD_ON_SHOULDER_CAR_STOPPED">HAZARD_ON_SHOULDER_CAR_STOPPED</option><option value="HAZARD_WEATHER">HAZARD_WEATHER</option>';
		var ROAD_CLOSED = '<option value="ROAD_CLOSED_CONSTRUCTION">ROAD_CLOSED_CONSTRUCTION</option><option value="ROAD_CLOSED_EVENT">ROAD_CLOSED_EVENT</option><option value="ROAD_CLOSED_HAZARD">ROAD_CLOSED_HAZARD</option>';
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
	$data["active"] = 0;
	$data['starttime'] = $data['starttime'] . $data['timezone_offset'];
	$data['endtime'] = $data['endtime'] . $data['timezone_offset'];
	unset($data['timezone_offset']);
	
	if($last_id = mysql_write_row("feed", $data)) header("Location: /#success-". $last_id);
	
		
}
?>


