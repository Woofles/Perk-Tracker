<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perk Stats</title>
    <style>
    svg > text {
        display: none;
    }
    </style>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>

    <?php require 'stats_db.php'; ?>

    <?php 

        date_default_timezone_set ( "America/Chicago" );

        /* Current Points */

        $stmt2 = $mysqli->prepare('SELECT (current_points) from perk_stats ORDER BY time DESC LIMIT 1');
        if(!$stmt2) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $stmt2->execute();
        $stmt2->bind_result($title_points);
        $stmt2->fetch();
        $title = $title_points;
        $stmt2->close();

        /* Current Points Graph */

        $stmt = $mysqli->prepare('SELECT * from perk_stats ORDER BY time DESC LIMIT 288');
        if(!$stmt) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $stmt->execute();
        $stmt->bind_result($id, $time, $curr, $total);

        $results = array();

        while($stmt->fetch()) {
            $eTime = strtotime($time);
            $jsTime = $eTime * 1000;
            array_push($results, array($jsTime, $curr));
        }

        $results = array_reverse($results);

        $stmt->close();

        /* Total Points Graph */

        $stmt = $mysqli->prepare('SELECT * from perk_stats ORDER BY time ASC');
        if(!$stmt) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $stmt->execute();
        $stmt->bind_result($id, $time, $curr, $total);

        $total_results = array();

        while($stmt->fetch()) {
            $eTime = strtotime($time);
            $jsTime = $eTime * 1000;
            array_push($total_results, array($jsTime, $total));
        }

        $stmt->close();

        /* Points Chart */

        $chart_results = array();

        $stmt = $mysqli->prepare('SELECT * from perk_stats ORDER BY time DESC LIMIT 288');
        if(!$stmt) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $stmt->execute();
        $stmt->bind_result($id, $time, $curr, $total);

        $past = 0;
        while($stmt->fetch()) {
            array_push($chart_results, array($time, $curr, $total, 0));
        }

        $stmt->close();        

        $past = (int) ($chart_results[0][1]);

        for($i = 1; $i < count($chart_results); $i++) {
            $curr = (int) ($chart_results[$i][1]);
            $chart_results[$i-1][3] = -($curr - $past);
            $past = $curr;
        }
        $chart_results[count($chart_results)-1][3] = "";

        $avg = 0;
        $total = 0;
        foreach($chart_results as $val) {
            $change = $val[3];
            if($change > 0 && $change < 100) {
                $avg += $change;
                $total++;
            }
        }
        $day_total = $avg;
        $avg /= $total;
        $avg = round($avg,2);

        /* Change Chart */

        $change_results = array();

        $stmt = $mysqli->prepare('SELECT * from perk_stats ORDER BY time DESC LIMIT 289');
        if(!$stmt) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $stmt->execute();
        $stmt->bind_result($id, $time, $curr, $total);

        $past = 0;
        while($stmt->fetch()) {
            $eTime = strtotime($time);
            $jsTime = $eTime * 1000;
            array_push($change_results, array($jsTime, $curr, $total, 0));
        }

        $stmt->close();        

        $past = (int) ($change_results[0][1]);

        for($i = 1; $i < count($change_results); $i++) {
            $curr = (int) ($change_results[$i][1]);
            $change_results[$i-1][3] = -($curr - $past);
            $past = $curr;
        }

        $change_results[count($change_results)-1][3] = 1.1;

        $new_change_results = array();

        foreach($change_results as $val) {
            if($val[3] == floor($val[3]) && $val[3] >= 0 && $val[3] <= 100) {
                array_push($new_change_results, array($val[0],$val[3]));
            }
        }

        $new_change_results = array_reverse($new_change_results);

    ?>

    <div class="container">
    <div class="col-md-12" style="text-align: center;">
        <h1>Perk Points Tracker</h1>
    </div>
    </div>

    <div class="container">
    <div class="col-md-12" style="text-align: center;">
        <?php echo("<h3>Current: ".$title." points </h3>"); ?>
    </div>
    <div class="col-md-12">
      <ul class="nav nav-tabs nav-justified">
        <li id="points-pill" class="active"><a href="javascript:void(0);" id="points-link">Points</a>
        <li id="change-pill"><a href="javascript:void(0);" id="change-link">Change</a>
        <li id="total-pill"><a href="javascript:void(0);" id="total-link">Total</a>
      </ul>
    </div>
    </div>

    <div class="container">
    <div class="col-md-12">
    <div class="highchart-container">

    </div>
    </table>
    </div>
    </div>

    <div class="container">
    <div class="col-md-3">
    </div>
    <div class="col-md-6">
    <?php echo("<h3 style='text-align:center;'>24 Hour Total: ".$day_total."</h3>"); ?>
    </div>
    </div>
    <div class="container">
    <div class="col-md-3">
    </div>
    <div class="col-md-6">
    <?php echo("<h3 style='text-align:center;'>Average points per 5 minutes: ".$avg."</h3>"); ?>
    </div>
    </div>
    <div class="container">
    <table class="table">
    <tr>
    <th>Time</th>
    <th>Current Points</th>
    <th>Change</th>
    <th>Total Points</th>
    <th>Total Money</th>
    </tr>
    <?php foreach($chart_results as $val) { ?>
        <tr>
        <td><?php echo($val[0]); ?></td>
        <td><?php echo($val[1]); ?></td>
        <?php $thing = $val[3]; ?>
        <?php
            if($thing == "") {
                echo("<td>N/A");
            } elseif($thing > 0) {
                echo("<td class='bg-success'>");
                echo("+".$thing);
            } elseif($thing == 0) {
                echo("<td class='bg-danger'>");
                echo($thing);
            } elseif($thing < 0) {
                echo("<td class='bg-info'>");
                echo($thing); 
            }
        ?>
        </td>
        <td><?php echo($val[2]); ?></td>
        <td>$<?php echo(floor(((int)$val[2])/1000)); ?></td>
        </tr>
    <?php } ?>
    </table>
    </div>
    <div class="col-md-3">
    </div>
    </div>
  </body>
  <footer>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="/stats/js/highcharts.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    <script type="text/javascript">

        $(function() {

            pointGraph();

            $('#points-link').click(function() { pointGraph(); });
            $('#change-link').click(function() { changeGraph(); });
            $('#total-link').click(function() { totalGraph(); });

            function pointGraph() {

                setTabs($('#points-pill'));

                var complex = <?php echo json_encode($results); ?>;

                $('.highchart-container').highcharts({
                    title: {
                        text: 'Perk Points',
                        x: -20 //center
                    },
                    xAxis: {
                        type: 'datetime',
                        dateTimeLabelFormats: { // don't display the dummy year
                            month: '%b %e',
                            year: '%b'
                        },
                        title: {
                            text: 'Date'
                        }
                    },
                    yAxis: {
                        title: {
                            text: 'Points'
                        },
                        plotLines: [{
                            value: 0,
                            width: 1,
                            color: '#808080'
                        }]
                    },
                    legend: {
                        enabled: false,
                    },
                    series: [{
                        name: 'Current',
                        data: complex
                    }]
                });
            }

            function changeGraph() {

                setTabs($('#change-pill'));

                var complex = <?php echo json_encode($new_change_results); ?>;

                $('.highchart-container').highcharts({
                    title: {
                        text: 'Perk Points per 5 Minutes',
                        x: -20 //center
                    },
                    xAxis: {
                        type: 'datetime',
                        dateTimeLabelFormats: { // don't display the dummy year
                            month: '%b %e',
                            year: '%b'
                        },
                        title: {
                            text: 'Date'
                        }
                    },
                    yAxis: {
                        title: {
                            text: 'Change'
                        },
                        plotLines: [{
                            value: 0,
                            width: 1,
                            color: '#808080'
                        }]
                    },
                    legend: {
                        enabled: false,
                    },
                    series: [{
                        name: 'Change',
                        data: complex
                    }]
                });
            }

            function totalGraph() {

                setTabs($('#total-pill'));

                var complex = <?php echo json_encode($total_results); ?>;

                $('.highchart-container').highcharts({
                    title: {
                        text: 'Total Perk Points',
                        x: -20 //center
                    },
                    xAxis: {
                        type: 'datetime',
                        dateTimeLabelFormats: { // don't display the dummy year
                            month: '%b %e',
                            year: '%b'
                        },
                        title: {
                            text: 'Date'
                        }
                    },
                    yAxis: {
                        title: {
                            text: 'Points'
                        },
                        plotLines: [{
                            value: 0,
                            width: 1,
                            color: '#808080'
                        }]
                    },
                    plotOptions: {
                        series: {
                            turboThreshold: 0,
                        },
                    },
                    legend: {
                        enabled: false,
                    },
                    series: [{
                        name: 'Total',
                        data: complex
                    }]
                });
            }

            function setTabs(current) {

                $('.highchart-container').empty();

                $('#points-pill').removeClass("active");
                $('#change-pill').removeClass("active");
                $('#total-pill').removeClass("active");

                current.addClass("active");
            }

        });

    </script>
  </footer>
</html>