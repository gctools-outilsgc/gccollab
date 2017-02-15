<?php

    $title = elgg_echo("gccollab_stats:title");

    $body = elgg_view_layout('one_column', array(
        'content' => get_stats(),
        'title' => $title,
    ));

    echo elgg_view_page($title, $body);

    function get_stats(){
        function compare_func($a, $b){
            return ($a[0] - $b[0]);
        }

        // Get GCcollab API data
        $json_raw = file_get_contents('https://api.gctools.ca/gccollab.ashx');
        $json = json_decode($json_raw, true);

        $count = $regGC = $regOrg = 0;

        // Get data ready for Member Registration Highcharts
        $registrations = array();
        foreach( $json as $key => $value ){
            if( $value['RegisteredSmall'] ){
            $count += $value['cnt'];
                $registrations[] = array(strtotime($value['RegisteredSmall']) * 1000, $count, $value['cnt']);
            }
        }
        usort($registrations, "compare_func");

        ob_start(); ?>

        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <script src="//code.highcharts.com/highcharts.js"></script>
        <script src="//code.highcharts.com/modules/exporting.js"></script>
        <script src="//code.highcharts.com/modules/data.js"></script>
        <script src="//code.highcharts.com/modules/drilldown.js"></script>
        <script src="//highcharts.github.io/export-csv/export-csv.js"></script>

        <div id="registrations" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

        <script>
            $(function () {
                Date.prototype.niceDate = function() {
                    var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                    var mm = this.getMonth();
                    var dd = this.getDate();
                    var yy = this.getFullYear();
                    return months[mm] + ' ' + dd + ', ' + yy;
                };

                var registrations = <?php echo json_encode($registrations); ?>;
                Highcharts.chart('registrations', {
                    chart: {
                        zoomType: 'x',
                        resetZoomButton: {
                            position: {
                                align: 'left',
                                x: 10,
                            },
                            relativeTo: 'chart'
                        }
                    },
                    title: {
                        text: '<?php echo elgg_echo("gccollab_stats:registration:title") . " (" . $count . ")"; ?>'
                    },
                    subtitle: {
                        text: document.ontouchstart === undefined ? 'Click and drag in the plot area to zoom in' : 'Pinch the chart to zoom in'
                    },
                    xAxis: {
                        type: 'datetime'
                    },
                    yAxis: {
                        title: {
                            text: '<?php echo elgg_echo("gccollab_stats:membercount"); ?>'
                        },
                        floor: 0
                    },
                    legend: {
                        enabled: false
                    },
                    plotOptions: {
                        area: {
                            fillColor: {
                                linearGradient: {
                                    x1: 0,
                                    y1: 0,
                                    x2: 0,
                                    y2: 1
                                },
                                stops: [
                                    [0, Highcharts.getOptions().colors[0]],
                                    [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                                ]
                            },
                            marker: {
                                radius: 2
                            },
                            lineWidth: 1,
                            states: {
                                hover: {
                                    lineWidth: 1
                                }
                            },
                            threshold: null
                        }
                    },
                    tooltip: {
                        formatter: function() {
                            return '<b>Date:</b> ' + new Date(registrations[this.series.data.indexOf(this.point)][0]).niceDate()
                            	+ '<br /><b>Signups:</b> ' + registrations[this.series.data.indexOf(this.point)][2]
                            	+ '<br /><b>Total:</b> ' + registrations[this.series.data.indexOf(this.point)][1];
                        }
                    },
                    series: [{
                        type: 'area',
                        name: 'Registered Members',
                        data: registrations
                    }]
                });
            });
        </script>

        <br />

    <ul id="stats-nav" class="nav nav-tabs">
        <li role="presentation" class="active"><a href="#all" aria-controls="all" role="tab" data-toggle="tab"><?php echo elgg_echo("gccollab_stats:types:title"); ?></a></li>
        <li role="presentation"><a href="#federal" aria-controls="federal" role="tab" data-toggle="tab"><?php echo elgg_echo("gccollab_stats:federal:title"); ?></a></li>
        <li role="presentation"><a href="#provincial" aria-controls="provincial" role="tab" data-toggle="tab"><?php echo elgg_echo("gccollab_stats:provincial:title"); ?></a></li>
        <li role="presentation"><a href="#student" aria-controls="student" role="tab" data-toggle="tab"><?php echo elgg_echo("gccollab_stats:student:title"); ?></a></li>
        <li role="presentation"><a href="#academic" aria-controls="academic" role="tab" data-toggle="tab"><?php echo elgg_echo("gccollab_stats:academic:title"); ?></a></li>
        <li role="presentation"><a href="#other" aria-controls="other" role="tab" data-toggle="tab"><?php echo elgg_echo("gccollab_stats:other:title"); ?></a></li>
    </ul>

    <div class="tab-content" style="width: 100%; max-width:100%;">
        <div role="tabpanel" class="tab-pane active" id="all">

    <?php
        // Get 'all' member API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=member.stats&type=all&lang=' . get_current_language());
        $json = json_decode($json_raw, true);

        $allMembers = array();
        $allMembersCount = $unknownCount = 0;
        foreach( $json['result'] as $key => $value ){
            if($key != 'public_servant' && $key != ''){
                $allMembers[] = array(ucfirst($key), $value);
            } else {
                $unknownCount += $value;
            }
            $allMembersCount += $value;
        }
        if($unknownCount > 0){ $allMembers[] = array(elgg_echo('gccollab_stats:unknown'), $unknownCount); }
        sort($allMembers);
    ?>

        <div id="allMembers" style="width: 100%; max-width:100%; min-height: 350px; margin: 0 auto"></div>

        <script>
            $(function () {
                var allMembers = <?php echo json_encode($allMembers); ?>;
                Highcharts.chart('allMembers', {
                    chart: {
                        type: 'bar'
                    },
                    title: {
                        text: '<?php echo elgg_echo("gccollab_stats:types:title") . " (" . $allMembersCount . ")"; ?>'
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: {
                        title: {
                            text: '<?php echo elgg_echo("gccollab_stats:membercount"); ?>'
                        }

                    },
                    legend: {
                        enabled: false
                    },
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                                format: '{point.y}'
                            }
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style=\"font-size:11px\">{series.name}</span><br>',
                        pointFormat: '<span style=\"color:{point.color}\">{point.name}</span>: <b>{point.y}</b> users<br/>'
                    },
                    series: [{
                        name: 'Member Type',
                        colorByPoint: true,
                        data: allMembers
                    }]
                });
            });
        </script>
        </div>

        <div role="tabpanel" class="tab-pane" id="federal">
        
    <?php
        // Get 'federal' member API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=member.stats&type=federal&lang=' . get_current_language());
        $json = json_decode($json_raw, true);

        $federalMembers = array();
        $federalMembersCount = $unknownCount = 0;
        foreach( $json['result'] as $key => $value ){
            if($key != 'default_invalid_value' && $key != ''){
                $federalMembers[] = array(ucfirst($key), $value);
            } else {
                $unknownCount += $value;
            }
            $federalMembersCount += $value;
        }
        if($unknownCount > 0){ $federalMembers[] = array(elgg_echo('gccollab_stats:unknown'), $unknownCount); }
        sort($federalMembers);
    ?>

        <div id="federalMembers" style="width: 100%; max-width:100%; min-height: 1000px; margin: 0 auto"></div>

        <script>
            $(function () {
                var federalMembers = <?php echo json_encode($federalMembers); ?>;
                Highcharts.chart('federalMembers', {
                    chart: {
                        type: 'bar'
                    },
                    title: {
                        text: '<?php echo elgg_echo("gccollab_stats:federal:title") . " (" . $federalMembersCount . ")"; ?>'
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: {
                        title: {
                            text: '<?php echo elgg_echo("gccollab_stats:membercount"); ?>'
                        }

                    },
                    legend: {
                        enabled: false
                    },
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                                format: '{point.y}'
                            }
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style=\"font-size:11px\">{series.name}</span><br>',
                        pointFormat: '<span style=\"color:{point.color}\">{point.name}</span>: <b>{point.y}</b> users<br/>'
                    },
                    series: [{
                        name: 'Department',
                        colorByPoint: true,
                        data: federalMembers
                    }]
                });
            });
        </script>
        </div>

        <div role="tabpanel" class="tab-pane" id="provincial">

    <?php
        // Get 'provincial' member API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=member.stats&type=provincial&lang=' . get_current_language());
        $json = json_decode($json_raw, true);

        $provincialMembers = $provincialMembersMinistry = $provincialMembersDrilldown = array();
        $provincialMembersCount = 0;
        foreach( $json['result'] as $key => $value ){
            $provincialMembers[] = array('name' => $key, 'y' => $value['total'], 'drilldown' => $key);
            $provincialMembersMinistry[$key] += $value['total'];
            $provincialMembersCount += $value['total'];

            $provinceData = array();
            $unknownCount = 0;
            foreach( $value as $ministry => $count ){
                if($ministry != 'total' && $ministry != 'default_invalid_value' && $ministry != ''){
                    $provinceData[] = array($ministry, $count);
                } else if($ministry === 'default_invalid_value' || $ministry === ''){
                    $unknownCount += $count;
                }
            }
            if($unknownCount > 0){ $provinceData[] = array(elgg_echo('gccollab_stats:unknown'), $unknownCount); }
            sort($provinceData);
            $provincialMembersDrilldown[] = array('name' => $key, 'id' => $key, 'data' => $provinceData);
        }
        sort($provincialMembers);
        sort($provincialMembersDrilldown);
    ?>

        <div id="provincialMembers" style="width: 100%; max-width:100%; min-height: 400px; margin: 0 auto"></div>

        <script>
            $(function () {
                var provincialMembers = <?php echo json_encode($provincialMembers); ?>;
                var provincialMembersDrilldown = <?php echo json_encode($provincialMembersDrilldown); ?>;
                Highcharts.chart('provincialMembers', {
                    chart: {
                        type: 'bar'
                    },
                    title: {
                        text: '<?php echo elgg_echo("gccollab_stats:provincial:title") . " (" . $provincialMembersCount . ")"; ?>'
                    },
                    subtitle: {
                        text: 'Click the columns to view the ministries within the province/territory.'
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: {
                        title: {
                            text: '<?php echo elgg_echo("gccollab_stats:membercount"); ?>'
                        }

                    },
                    legend: {
                        enabled: false
                    },
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                                format: '{point.y}'
                            }
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style=\"font-size:11px\">{series.name}</span><br>',
                        pointFormat: '<span style=\"color:{point.color}\">{point.name}</span>: <b>{point.y}</b> users<br/>'
                    },
                    series: [{
                        name: 'Province/Territory',
                        colorByPoint: true,
                        data: provincialMembers
                    }],
                    drilldown: {
                        series: provincialMembersDrilldown
                    }
                });
            });
        </script>
        </div>

        <div role="tabpanel" class="tab-pane" id="student">

    <?php
        // Get 'student' member API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=member.stats&type=student&lang=' . get_current_language());
        $json = json_decode($json_raw, true);

        $studentMembers = $studentMembersDrilldown = array();
        $studentMembersCount = 0;
        $institutionName = (get_current_language() == "fr") ? array("college" => "Collège", "university" => "Université") : array("college" => "College", "university" => "University");
        foreach( $json['result'] as $key => $value ){
            if($key == 'college' || $key == 'university'){
                $studentMembers[] = array('name' => $institutionName[$key], 'y' => $value['total'], 'drilldown' => $institutionName[$key]);
                $studentMembersCount += $value['total'];
            }

            $institutionData = array();
            foreach( $value as $school => $count ){
                if($school != 'total') $institutionData[] = array($school, $count);
            }
            sort($institutionData);
            $studentMembersDrilldown[] = array('name' => $institutionName[$key], 'id' => $institutionName[$key], 'data' => $institutionData);
        }
        sort($studentMembers);
        sort($studentMembersDrilldown);
    ?>

        <div id="studentMembers" style="width: 100%; max-width:100%; min-height: 800px; margin: 0 auto"></div>

        <script>
            $(function () {
                var studentMembers = <?php echo json_encode($studentMembers); ?>;
                var studentMembersDrilldown = <?php echo json_encode($studentMembersDrilldown); ?>;
                Highcharts.chart('studentMembers', {
                    chart: {
                        type: 'bar'
                    },
                    title: {
                        text: '<?php echo elgg_echo("gccollab_stats:student:title") . " (" . $studentMembersCount . ")"; ?>'
                    },
                    subtitle: {
                        text: 'Click the columns to view the various schools.'
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: {
                        title: {
                            text: '<?php echo elgg_echo("gccollab_stats:membercount"); ?>'
                        }

                    },
                    legend: {
                        enabled: false
                    },
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                                format: '{point.y}'
                            }
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style=\"font-size:11px\">{series.name}</span><br>',
                        pointFormat: '<span style=\"color:{point.color}\">{point.name}</span>: <b>{point.y}</b> users<br/>'
                    },
                    series: [{
                        name: 'Institution',
                        colorByPoint: true,
                        data: studentMembers
                    }],
                    drilldown: {
                        series: studentMembersDrilldown
                    },
                    colors: ['#7cb5ec', '#f45b5b']
                });
            });
        </script>
        </div>

        <div role="tabpanel" class="tab-pane" id="academic">

    <?php
        // Get 'academic' member API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=member.stats&type=academic&lang=' . get_current_language());
        $json = json_decode($json_raw, true);

        $academicMembers = $academicMembersDrilldown = array();
        $academicMembersCount = 0;
        $institutionName = (get_current_language() == "fr") ? array("college" => "Collège", "university" => "Université") : array("college" => "College", "university" => "University");
        foreach( $json['result'] as $key => $value ){
            if($key == 'college' || $key == 'university'){
                $academicMembers[] = array('name' => $institutionName[$key], 'y' => $value['total'], 'drilldown' => $institutionName[$key]);
                $academicMembersCount += $value['total'];
            }

            $institutionData = array();
            foreach( $value as $school => $count ){
                if($school != 'total') $institutionData[$school] = array($school, $count);
            }
            sort($institutionData);
            // Reducing amount of universities by half fixes the display issue, HighCharts unable to show all
            // $institutionData = array_slice($institutionData, count($institutionData) / 2);
            $academicMembersDrilldown[] = array('name' => $institutionName[$key], 'id' => $institutionName[$key], 'data' => $institutionData);
        }
        sort($academicMembers);
        sort($academicMembersDrilldown);
    ?>

        <div id="academicMembers" style="width: 100%; max-width:100%; min-height: 800px; margin: 0 auto"></div>

        <script>
            $(function () {
                var academicMembers = <?php echo json_encode($academicMembers); ?>;
                var academicMembersDrilldown = <?php echo json_encode($academicMembersDrilldown); ?>;
                Highcharts.chart('academicMembers', {
                    chart: {
                        type: 'bar'
                    },
                    title: {
                        text: '<?php echo elgg_echo("gccollab_stats:academic:title") . " (" . $academicMembersCount . ")"; ?>'
                    },
                    subtitle: {
                        text: 'Click the columns to view the various schools.'
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: {
                        title: {
                            text: '<?php echo elgg_echo("gccollab_stats:membercount"); ?>'
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                                format: '{point.y}'
                            }
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style=\"font-size:11px\">{series.name}</span><br>',
                        pointFormat: '<span style=\"color:{point.color}\">{point.name}</span>: <b>{point.y}</b> users<br/>'
                    },
                    series: [{
                        name: 'Institution',
                        colorByPoint: true,
                        data: academicMembers
                    }],
                    drilldown: {
                        series: academicMembersDrilldown
                    },
                    colors: ['#7cb5ec', '#f45b5b']
                });
            });
        </script>
        </div>

        <div role="tabpanel" class="tab-pane" id="other">

    <?php
        // Get 'other' member API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=member.stats&type=other&lang=' . get_current_language());
        $json = json_decode($json_raw, true);

        $otherMembers = array();
        $otherMembersCount = 0;
        foreach( $json['result'] as $key => $value ){
            if($key != 'total'){
                $otherMembers[] = array(ucfirst($key), $value);
                $otherMembersCount += $value;
            }
        }
        sort($otherMembers);
    ?>

        <div id="otherMembers" style="width: 100%; max-width:100%; min-height: 350px; margin: 0 auto"></div>

        <script>
            $(function () {
                var otherMembers = <?php echo json_encode($otherMembers); ?>;
                Highcharts.chart('otherMembers', {
                    chart: {
                        type: 'bar'
                    },
                    title: {
                        text: '<?php echo elgg_echo("gccollab_stats:other:title") . " (" . $otherMembersCount . ")"; ?>'
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: {
                        title: {
                            text: '<?php echo elgg_echo("gccollab_stats:membercount"); ?>'
                        }

                    },
                    legend: {
                        enabled: false
                    },
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                                format: '{point.y}'
                            }
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style=\"font-size:11px\">{series.name}</span><br>',
                        pointFormat: '<span style=\"color:{point.color}\">{point.name}</span>: <b>{point.y}</b> users<br/>'
                    },
                    series: [{
                        name: 'Other Members',
                        colorByPoint: true,
                        data: otherMembers
                    }]
                });
            });
        </script>
        </div>

    </div>

    <script>
        $(function () {
            $("#stats-nav li a").click(function() {
                setTimeout(function(){
                    $("#registrations").highcharts().reflow();
                    $("#allMembers").highcharts().reflow();
                    $("#federalMembers").highcharts().reflow();
                    $("#provincialMembers").highcharts().reflow();
                    $("#studentMembers").highcharts().reflow();
                    $("#academicMembers").highcharts().reflow();
                    $("#otherMembers").highcharts().reflow();
                }, 1);
            });
        });
    </script>

    <?php

        $display = ob_get_clean();

        // Use code below to see which profiles are missing data (aka show up as 'Unknown')
        /*
        ini_set("memory_limit", -1);
        $users = elgg_get_entities_from_metadata(array(
            'type' => 'user',
            'limit' => 0
        ));
        $otherCount = 0;
        echo "<ul>";
        foreach($users as $user){
            if($user->user_type == "public_servant" || $user->user_type == ""){
                echo "<li><a href='" . elgg_get_site_url() . "profile/" . $user->username . "' target='_blank'>" . $user->username . "</a></li>";
                $otherCount++;
            }
        }
        echo "</ul>Count: " . $otherCount;
        */

        return $display;
    }