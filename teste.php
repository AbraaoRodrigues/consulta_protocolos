<?php
include 'conexao.php';

$query = ibase_query($conn, "SELECT FIRST 1 * FROM PRTPROTO");

$row = ibase_fetch_assoc($query);

echo "<pre>";
print_r($row);
echo "</pre>";
