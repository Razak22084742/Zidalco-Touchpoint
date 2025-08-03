<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit;
}

require_once "../config/db.php";

$database = "zidalco"; // change to your DB name

header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="'.$database.'_backup_'.date("Y-m-d_H-i-s").'.sql"');

$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

foreach ($tables as $table) {
    $res = $conn->query("SELECT * FROM $table");
    $createTable = $conn->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_ASSOC);
    echo "DROP TABLE IF EXISTS `$table`;\n";
    echo $createTable['Create Table'].";\n\n";

    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $vals = array_map([$conn, 'quote'], array_values($row));
        echo "INSERT INTO `$table` VALUES(".implode(',', $vals).");\n";
    }
    echo "\n\n";
}
exit;
?>
