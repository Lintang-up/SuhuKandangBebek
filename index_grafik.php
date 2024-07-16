<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="icon.png" type="image/x-icon"/>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css" rel="stylesheet">
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-more.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    
    <title>Monitoring Suhu Ruang Kandang</title>
</head>
<body class="mt-2 mb-5">
        <?php
        include "koneksi.php";

        // Get temperature and humidity data from database (interval last 7 day)
        $sql = "SELECT tgl, jam, suhu, kelembaban, lampu, kipas FROM sensor_suhu WHERE tgl BETWEEN (SELECT MAX(tgl) - INTERVAL 6 DAY FROM sensor_suhu) AND (SELECT MAX(tgl) FROM sensor_suhu) ORDER BY id DESC";
        $result = mysqli_query($koneksi, $sql);
        
        $sqlLatest = "SELECT tgl, jam, lampu, kipas FROM sensor_suhu ORDER BY id DESC LIMIT 1";
        $resultLatest = mysqli_query($koneksi, $sqlLatest);
        $latestData = mysqli_fetch_assoc($resultLatest);
        
        $data = array();
        while ($row = $result->fetch_assoc()) {
            $dateTime = date('Y-m-d H:i:s', strtotime($row['tgl'] . ' ' . $row['jam']));
            $data[] = array($dateTime, (float)$row['suhu'], (float)$row['kelembaban']);
        }
        
        usort($data, function($a, $b) {
            return strtotime($a[0]) - strtotime($b[0]);
        });
        
        // Data for On/Off Kipas and Lampu
        $sqlOnOff = "SELECT tgl, jam, lampu, kipas FROM sensor_suhu WHERE tgl BETWEEN (SELECT MAX(tgl) - INTERVAL 6 DAY FROM sensor_suhu) AND (SELECT MAX(tgl) FROM sensor_suhu) ORDER BY id DESC";
        $resultOnOff = mysqli_query($koneksi, $sqlOnOff);
        
        $dataOnOff = array();
        while ($rowOnOff = mysqli_fetch_assoc($resultOnOff)) {
            $dateTimeOnOff = date('Y-m-d H:i:s', strtotime($rowOnOff['tgl'] . ' ' . $rowOnOff['jam']));
            $dataOnOff[] = array($dateTimeOnOff, (int)$rowOnOff['lampu'], (int)$rowOnOff['kipas']);
        }
        
        usort($dataOnOff, function($a, $b) {
            return strtotime($a[0]) - strtotime($b[0]);
        });
        
        // Check if the data is not updated for 24 hours
        $lastDataDateTime = date($latestData['tgl'] . ' ' . $latestData['jam']);
        $currentDateTime = date('Y-m-d H:i:s');
        
        // Compute the time difference between the data's last datetime and the current datetime in seconds
        $timeDiff = strtotime($currentDateTime) - strtotime($lastDataDateTime);
        
        // Set NodeMCU status based on data update time
        $nodeMCUStatus = ($timeDiff <= 24 * 60 * 60) ? "ON" : "OFF";
        
        // Toggle NodeMCU status if necessary
        if ($nodeMCUStatus === "OFF") {
            // Perform actions to turn off NodeMCU
            // ...
        }
    ?>
    <div class="container">
    <a href="index.php" class="btn btn btn-warning btn-md"><b>TABEL</b></a>
    <a href="#" class="btn btn-info btn-md"><b>GRAFIK</b></a>
    
        <div id="grafik" class="mt-2 mb-5">
            <h2>
                Grafik Monitoring Suhu dan Kelembaban
            </h2>
        </div>
        <div id="grafik_on_off" class="mt-2 mb-5">
            <h2>
                Grafik On/Off Kipas dan Lampu
            </h2>
        </div>
        <?php
            $lampu = $latestData['lampu'] === '1' ? "enabled" : "disabled";
            $kipas = $latestData['kipas'] === '1' ? "enabled" : "disabled";
        ?>
        <style>
            .badge.disabled {
            opacity: 0.65;
            }
            .badge.enabled {
            opacity: 1;
            }
        </style>
        <!-- Bagian Status -->
        <!-- Bagian Status -->
    </div>
    <script>
        var data = <?php echo json_encode($data); ?>;
    
        Highcharts.chart('grafik', {
            chart: {
                zoomType: 'x'
            },
            title: {
                text: 'Grafik Monitoring Suhu dan Kelembaban'
            },
            xAxis: {
                type: 'datetime',
                labels: {
                    formatter: function() {
                        return Highcharts.dateFormat('%e %b %Y<br>%H:%M', this.value);
                    }
                },
                dateTimeLabelFormats: {
                    year: '%Y',
                    month: '%b %Y',
                    day: '%e %b %Y',
                    hour: '%e %b %Y %H:%M'
                }
            },
            yAxis: [{
                title: {
                    text: 'SUHU (°C)'
                }
            }, {
                title: {
                    text: 'KELEMBABAN (%)'
                },
                opposite: true
            }],
            series: [{
                name: 'SUHU',
                color: 'green',
                type: 'spline',
                yAxis: 0,
                data: data.map(function (item) {
                    const suhuValue = item[1] === 0 ? NaN : item[1];
                    const date = new Date(item[0]);
                    date.setHours(date.getHours() - (date.getTimezoneOffset() / 60));
                    return [date.getTime(), suhuValue];
                }),
                marker: {
                    enabled: true,
                    radius: 2
                }
            }, {
                name: 'KELEMBABAN',
                color: 'blue',
                type: 'spline',
                yAxis: 1,
                data: data.map(function (item) {
                    const kelembabanValue = item[2] === 0 ? NaN : item[2];
                    const date = new Date(item[0]);
                    date.setHours(date.getHours() - (date.getTimezoneOffset() / 60));
                    return [date.getTime(), kelembabanValue];
                }),
                marker: {
                    enabled: true,
                    radius: 2
                }
            }]
        });
        
        // Data for On/Off Kipas and Lampu
        var dataOnOff = <?php echo json_encode($dataOnOff); ?>;
        
        Highcharts.chart('grafik_on_off', {
            chart: {
                zoomType: 'x',
            },
            title: {
                text: 'Grafik On/Off Lampu dan Kipas'
            },
            xAxis: {
                type: 'datetime',
                labels: {
                    formatter: function() {
                        return Highcharts.dateFormat('%e %b %Y<br>%H:%M', this.value);
                    }
                },
                dateTimeLabelFormats: {
                    year: '%Y',
                    month: '%b %Y',
                    day: '%e %b %Y',
                    hour: '%e %b %Y %H:%M'
                }
            },
            yAxis: {
                title: {
                    text: 'Status'
                },
                tickInterval: 1,
                labels: {
                    formatter: function() {
                        return this.value === 1 ? 'ON' : 'OFF';
                    }
                },
                tickPositions: [0, 1],
            },
            series: [{
                name: 'Lampu',
                color: 'orange',
                type: 'areaspline',
                data: dataOnOff.map(function (item) {
                    const status = item[1] === 1 ? 1 : 0;
                    const date = new Date(item[0]);
                    date.setHours(date.getHours() - (date.getTimezoneOffset() / 60));
                    return [date.getTime(), status];
                }),
                marker: {
                    enabled: false
                }
            },{
                name: 'Kipas',
                color: 'purple',
                type: 'areaspline',
                data: dataOnOff.map(function (item) {
                    const status = item[2] === 1 ? 1 : 0;
                    const date = new Date(item[0]);
                    date.setHours(date.getHours() - (date.getTimezoneOffset() / 60));
                    return [date.getTime(), status];
                }),
                marker: {
                    enabled: false
                },
            }]
        });
    </script>
</body>
</html>