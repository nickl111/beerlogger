<?php
// This literally just connects to the db. It is here so that the below can be filled in by the install script
$db = new mysqli('|SQL_HOST|', '|SQL_USER|', '|SQL_PASS|', '|SQL_DB|');
?>