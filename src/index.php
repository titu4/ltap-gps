<?php
$loc = dirname(__FILE__).'/sqlite_db/coord.db';
$db = new SQLite3($loc,SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
$raw = file_get_contents('php://input');
$raw = preg_replace('/\\x00/','',$raw);
$data = json_decode($raw);

if (!empty($data) && is_object($data) && property_exists($data,'lat') && property_exists($data,'lon')){
    if(file_exists($loc)) echo 'exists!'.chr(0xa);
    $src = 'SELECT name FROM sqlite_master WHERE type=\'table\' AND name=\'coordinates\'';
    $res = $db->querySingle($src);
    if (count($res)==0){
            $db->exec('CREATE TABLE coordinates (latitude TEXT, longitude TEXT, time TIMESTAMP DEFAULT CURRENT_TIMESTAMP, added TIMESTAMP DEFAULT CURRENT_TIMESTAMP ) ');
    }

$regex = '/^(|\-)([0-9]{2,3}\.[0-9]{0,8})$/';

if (preg_match($regex,$data->lat) && preg_match($regex,$data->lon) )
    {
	$lat = $data->lat;
	$lon = $data->lon;
    }
    $ins = 'INSERT INTO coordinates (latitude,longitude) VALUES (\''.SQLite3::escapeString($lat).'\',\''.SQLite3::escapeString($lon).'\')';
    $db->exec($ins);
    die();
}
?>

<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css" integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ==" crossorigin=""/>
  <script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js" integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw==" crossorigin=""></script>
</head>
<body>
<div id="map" style="width: 800px; height: 600px;"></div>
<script>
var map = L.map('map').setView([0,0], 4);
L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {attribution: '<a href="http://osm.org/copyright">OSM</a>'}).addTo(map);

<?php
    if($result = $db->query('SELECT latitude,longitude FROM coordinates ORDER BY added DESC LIMIT 10')){
    echo ' var latlngs = [ ';
    while($obj = $result->fetchArray()){
	if (!is_array($obj) || !isset($obj['latitude']) || !isset($obj['longitude']) || empty($obj['latitude']) || empty($obj['longitude'])) continue;
	echo '["'. $obj['latitude'].'","'.$obj['longitude'].'"],';
    }
    echo ']; ';
    } else
     echo('//'.$db->lastErrorMsg().chr(0xa));
     echo($data);
?>
var polyline = L.polyline(latlngs, {color: 'red'}).addTo(map);
map.fitBounds(polyline.getBounds());
</script>
</body>
</html>
