<?php
$dbh = new PDO('pgsql:host=localhost;dbname=postgres', "postgres", NULL);
echo "<table border='black'>";
echo "<tr><th>id</th><th>filler</th></tr>";
foreach($dbh->query("SELECT * FROM test") as $row) {
	echo "<tr>";
	echo "<td>";
	echo $row[filler];
	echo "</td>";
	echo "<td>";
	echo $row[id];
	echo "</td>";
	echo "</tr>";
}
echo "</table>";
$dbh = null;
?>
