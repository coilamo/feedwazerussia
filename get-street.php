<?php
$lat = $_GET['lat'];
$lon = $_GET['lon'];
$url="https://feed.world.waze.com/FeedManager/getStreet?token=WAZE_COMMUNITY_7c34df4e45&lat=" . $lat . "&lon=" . $lon . "&radius=50";
$json = file_get_contents($url);
$data = json_decode($json, true);
$name=$data['result'][0]['names'][0];
echo trim($name);
