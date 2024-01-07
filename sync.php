<?php
// read from ini file
$config = parse_ini_file("settings.ini", true);

if (!isset($config['MS-CONFIG']) || !isset($config['SLAVE']) || !isset($config['sync'])) {
    die("Settings not configure properly.");
}

// sync destination
$destination = $config['sync']['destination'];

// slave db connection
$slave_db = new mysqli($config['SLAVE']['hostname'], $config['SLAVE']['username'], $config['SLAVE']['password'], $config['SLAVE']['database']);

// if connection error
if ($slave_db->connect_errno) {
    die($slave_db->connect_error);
}

// get un-processed data from slave table
$query = $slave_db->query("SELECT * FROM attendance_log WHERE is_process = 0 LIMIT 10");

// final data
$data = [];

if($query->num_rows > 0){
	while ($row = $query->fetch_assoc()) {
	    $data[] = $row;
	}
} else {
	die("No data remaining for sync. All are up-to-date.");
}

// consume record id
$consume_record_ids = implode(',', array_column($data, "id"));

$data = json_encode($data);

// call cURL
$ch = curl_init();

$data = ['data' => $data];

curl_setopt($ch, CURLOPT_URL, $destination);
// curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_POST, 1);
// curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);    // 60 seconds
// curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$output = curl_exec($ch);

curl_close($ch);

$response = json_decode($output, TRUE);

if($response['status'] == 'success'){
	// update is_process = 1
	$query = $slave_db->query("UPDATE attendance_log SET is_process=1 WHERE id IN($consume_record_ids)");
}

die($response['message']);