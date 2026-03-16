<?php
// check_db.php
$serverName = "LAPTOP-C2NFDPF4";
$conn = sqlsrv_connect($serverName);

if ($conn === false) {
    die("❌ Error de conexión: " . print_r(sqlsrv_errors(), true));
}

$query = "SELECT name FROM sys.databases ORDER BY name";
$stmt = sqlsrv_query($conn, $query);

echo "<h2>Bases de datos disponibles en tu servidor:</h2><ul>";

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo "<li>" . $row['name'] . "</li>";
}

echo "</ul>";
sqlsrv_close($conn);
?>