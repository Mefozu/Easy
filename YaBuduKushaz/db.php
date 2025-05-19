<?php
$host = "localhost";
$user = "root"; //имя от phpMyAdmin
$password = ""; //пароль (если есть, обычно = root)
$dbname = "restaraunt"; //название вашей базы данных

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
?>