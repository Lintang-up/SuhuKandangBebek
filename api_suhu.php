<?php
include 'koneksi.php';

date_default_timezone_set('Asia/Jakarta');
$d = date("Y-m-d");
$t = date("H:i:s");

if (!empty($_POST['suhu']) && !empty($_POST['kelembaban'])) {
    $suhu = $_POST['suhu'];
    $kelembaban = $_POST['kelembaban'];
    
    if ($suhu === 'nan' && $kelembaban === 'nan') {
        $suhu = 'NaN';
        $kelembaban = 'NaN';
        
        // Matikan lampu dan kipas jika suhu NaN
        $lampu = 0;
        $kipas = 0;
    } else {
        // Periksa nilai suhu untuk mengontrol lampu dan kipas
        if ($suhu > 28) {
            // Suhu lebih dari 32, matikan lampu dan hidupkan kipas
            $lampu = 0;
            $kipas = 1;
        } else {
            // Suhu kurang dari 32, hidupkan lampu dan matikan kipas
            $lampu = 1;
            $kipas = 0;
        }
    }
    
    $cek = "SELECT suhu, kelembaban, lampu, kipas FROM sensor_suhu WHERE id = (SELECT MAX(id) FROM sensor_suhu)";
    $que = $koneksi->query($cek);
    $row = $que->fetch_assoc();
    
    $suhu_old = $row['suhu'];
    
    if ($suhu != $suhu_old) {
        // Masukkan data suhu, kelembaban, lampu, dan kipas ke dalam database
        $sql = "INSERT INTO sensor_suhu (tgl, jam, suhu, kelembaban, lampu, kipas) VALUES ('" . $d . "','" . $t . "','" . $suhu . "','" . $kelembaban . "','" . $lampu . "','" . $kipas . "')";
        
        if ($koneksi->query($sql) === TRUE) {
            echo "Data tersimpan";
        } else {
            echo "Error: " . $sql . "<br>" . $koneksi->error;
        }
    } else {
        echo "Tidak ada perubahan";
    }
} else {
    echo "Data gagal tersimpan";
}

$koneksi->close();
