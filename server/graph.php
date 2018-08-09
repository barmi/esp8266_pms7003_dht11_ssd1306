<?php
/**
 * Created by PhpStorm.
 * User: skshin
 * Date: 2018-08-03
 * Time: 오후 4:18
 */
include_once 'db.config';
$con= new mysqli($db_host, $db_user, $db_passwd, $db_dbname)or die("Could not connect to mysql".mysqli_error($con));
$con->query("set names utf8");

// default id & date
$id = 'junho';
$date = date("Y-m-d");
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}
if (isset($_GET['date'])) {
    $date = $_GET['date'];
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <!-- script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script -->
</head>
<body>
<form action="graph.php" method="get">
    id: <select name="id">
<?php
    //$sql = "SELECT DISTINCT year(time),month(time),day(time) FROM `iot`";
    $sql = "SELECT DISTINCT userid FROM `iot`";
    $result = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_array($result))
    {
        $sel_str = ($row['userid'] == $id) ? "selected" : "";
        echo "<option value='".$row['userid']."' ".$sel_str." >".$row['userid']."</option>";
    }
?>
    </select>
    date: <select name="date">
<?php
    $sql = "SELECT DISTINCT DATE_FORMAT(time, '%Y-%m-%d') as d FROM `iot`";
    $result = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_array($result))
    {
        $sel_str = ($row['d'] == $date) ? "selected" : "";
        echo "<option value='".$row['d']."' ".$sel_str." >".$row['d']."</option>";
    }

?>
    </select>
    <input type="submit" value="다시그리기" />
</form>
<div id="curve_chart" style="width: 100%; height: 600px"></div>

<script type="text/javascript">
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart(){
        var data = new google.visualization.DataTable();
        data.addColumn('timeofday','Time');
        data.addColumn('number','습도');
        data.addColumn('number','온도');
        data.addColumn('number','PM1.0');
        data.addColumn('number','PM2.5');
        data.addColumn('number','PM10');
        data.addRows([
<?php
/*
        data.addRows([
            [[03,02,07],270.26,298.40,111.54,228.06, 10],
            [[03,28,42],273.23,190.43,245.69,283.21, 20],
            [[07,26,04],144.33,217.26,206.53,167.68, 30],
            [[12,13,20],153.15,277.23,167.20,240.88, 40]
        ]);
*/
        //$date = "2018-08-02";
        $sql = "SELECT DATE_FORMAT(time, '%H') as h, DATE_FORMAT(time, '%i') as m, DATE_FORMAT(time, '%s') as s, "
                ."Humi, Temp, `PM1_0`, `PM2_5`, `PM10` "
                ."FROM `iot` "
                ."WHERE userid='".$id."' "
                ."AND (time BETWEEN '".$date." 00:00:00' and '".$date." 23:59:59') order by h,m,s";
        $result = mysqli_query($con, $sql);
        while ($row = mysqli_fetch_array($result))
        {
            $time_h = $row['h'];
            $time_m = $row['m'];
            $time_s = $row['s'];
            $Humi = $row['Humi'];
            $Temp = $row['Temp'];
            $PM1_0 = $row['PM1_0'];
            $PM2_5 = $row['PM2_5'];
            $PM10 = $row['PM10'];
            echo "[[".$time_h.",".$time_m.",".$time_s."],".$Humi.",".$Temp.",".$PM1_0.",".$PM2_5.",".$PM10."],";
        }
?>
        ]);
        var options = {
            title: '온도,습도,미세먼지 기록',
            legend: { position: 'bottom' },
            //width: 1000,
            //height: 600,
            smoothLine: true,
            curveType: 'function',
            hAxis: { format: 'hh:mm:ss' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
        chart.draw(data, options);

        function resizeChart() {
            chart.draw(data, options);
        }
        if (document.addEventListener) {
            window.addEventListener('resize', resizeChart);
        }
        else if (document.attachEvent) {
            window.attachEvent('onresize', resizeChart);
        }
        else {
            window.resize = resizeChart;
        }
    }
</script>
</body>
</html>