<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="icon.png" type="image/x-icon"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

    <!--datepicker-->
    <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.4.1/css/dataTables.dateTime.min.css">
    <script src="https://cdn.datatables.net/datetime/1.4.1/js/dataTables.dateTime.min.js"></script>

    <!--responsive-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script>
    <title>Monitoring Suhu Ruang Kandang</title>
</head>

<body class="mt-2 mb-5">

    <div class="container">
        <a href="#" class="btn btn btn-warning btn-md"><b>TABEL</b></a>
        <a href="index_grafik.php" class="btn btn-info btn-md"><b>GRAFIK</b></a>
        <h2 class="text-center"> Tabel Riwayat Monitoring</h2>
        <table id="tabel" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
            <thead class="text-white bg-success">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Suhu</th>
                    <th>Kelembaban</th>
                    <th>Lampu</th>
                    <th>Kipas</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include 'koneksi.php';
                $datasuhu = mysqli_query($koneksi, "SELECT * FROM sensor_suhu order by id desc");
                $no = 0;
                while ($d = mysqli_fetch_array($datasuhu)) {
                    $no++;
                ?>
                    <tr>
                        <td><?php echo $no; ?></td>
                        <td><?php echo date('d F Y', strtotime($d['tgl'])); ?></td>
                        <td><?php echo $d['jam']; ?></td>
                        <td><?php echo $d['suhu']; ?></td>
                        <td><?php echo $d['kelembaban']; ?></td>
                        <td><?php echo $d['lampu'] == 1 ? 'ON' : 'OFF'; ?></td>
                        <td><?php echo $d['kipas'] == 1 ? 'ON' : 'OFF'; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <!-- Searching and pembatas penampil tabel -->
    <script>
        $(document).ready(function() {
            var table = $('#tabel').DataTable({
                responsive: true
            });
            new DateTime(document.getElementById('from_date'));
            new DateTime(document.getElementById('to_date'));
        });
        setInterval(function() {
            // table.ajax.reload();
        }, 3000);
    </script>
</body>

</html>