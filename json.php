<?php
$lat = $_GET['lat'];
$lon = $_GET['lon'];
$obj = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/timezone/json?location=" . $lat . "," . $lon . "&timestamp=" . time() . "&key=AIzaSyAtNzSdKyxpLeQPQaVn5vdNK6qvo0SKJLg", true));
echo "+" . gmdate("H:i", $obj->rawOffset);
?>
