<?php

// PHP error off
// error_reporting(0);

// default time zone setup
date_default_timezone_set("Asia/Dhaka");

// read ini file
$config = parse_ini_file("settings.ini", true);

if (!isset($config['MS-CONFIG']) || !isset($config['SLAVE']) || !isset($config['sync'])) {
    die("Settings not configure properly.");
}

// MS Access file location
$source_db = "N/A";

if (isset($config['MS-CONFIG']) && $config['MS-CONFIG']['file_location']) {
    $source_db = realpath($config['MS-CONFIG']['file_location']);
} else {
    die("Device database file location not define. Please define the location.");
}

// file existance checking
if (!file_exists($source_db)) {
    die("Device file location is not correct. File not exists.");
}

// slave mysql location database connection
if (isset($config['SLAVE'])) {
    if (isset($config['SLAVE']['hostname']) && isset($config['SLAVE']['database']) && isset($config['SLAVE']['username']) && isset($config['SLAVE']['password'])) {

        // MySQLi connection
        $slave_db = new mysqli($config['SLAVE']['hostname'], $config['SLAVE']['username'], $config['SLAVE']['password'], $config['SLAVE']['database']);

        // if database connection error
        if ($slave_db->connect_errno > 0) {
            die("Error!! : " . $slave_db->connect_error);
        }
    } else {
        die("Slave database connection error. Hostname or database or username or password not set.");
    }
} else {
    die("Slave database connection not set. Please set it first.");
}



// pdo db connection

try {
    $raw_db = new PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb, *.accdb)}; DBQ=$source_db; Uid=; Pwd=;");
    $raw_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

$sql = "SELECT TOP 10 CKUT.CHECKTIME, CKUT.sn, CKUT.is_process, CKUT.id, UF.Badgenumber AS USERID FROM CHECKINOUT CKUT INNER JOIN USERINFO UF ON UF.USERID = CKUT.USERID WHERE CKUT.is_process = 0";

$result = $raw_db->query($sql);

$data = $result->fetchAll(PDO::FETCH_ASSOC);

$success = 0;

if (count($data) > 0) {
    foreach ($data as $item) {

        // initialize variable
        $user_id              = (int) $item['USERID'];
        $att_date             = date('Y-m-d', strtotime($item['CHECKTIME']));
        $mechine_serial_no    = $item['sn'];
        $raw_id               = $item['id'];
        $attendance_date_time = $item['CHECKTIME'];

        $total_entry = $slave_db->query("SELECT count(*) as total_entry FROM attendance_log ATLG WHERE user_id = ${user_id} AND DATE(attendance_date) = '${att_date}'")->fetch_assoc()['total_entry'];

        // 1 = IN, 2 = OUT
        $status = ($total_entry % 2 == 0) ? 1 : 2;

        // start transaction
        $slave_db->begin_transaction();

        // auto commit off
        $slave_db->autocommit(false);

        // execute insert query
        $slave_sql = "INSERT INTO attendance_log VALUES('', '${mechine_serial_no}', ${raw_id}, ${user_id}, '${attendance_date_time}', $status, 0)";

        $slave_db->query($slave_sql);

        // execute device db
        $raw_sql = "UPDATE CHECKINOUT SET is_process=1 WHERE id=?";
        $update = $raw_db->prepare($raw_sql)->execute([$raw_id]);

        if ($slave_db->commit() && $update) {
            $success++;

            // success the commit
            echo "Success!!! Attendance record imported from device db.<br/>";
        } else {
            // rollback
            $slave_db->rollback();
            echo "Error!!! Fail to import data from device db.<br/>";
        }
    }

    if ($success > 0) {
        die("Total <b>" . $success . "</b> record updated.");
    } else {
        die("No data imported due db error.");
    }
} else {
    die("No new record found to update.");
}
