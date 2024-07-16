<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "suhu_bebek";

$koneksi = mysqli_connect($servername, $username, $password, $dbname);
if (!$koneksi){
    die("Koneksi Gagal :".mysqli_connect_error());
}
?>