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

        <script src="https://code.highcharts.com/highcharts.js"></script>
        <script src="https://code.highcharts.com/modules/exporting.js"></script>
        <script src="https://code.highcharts.com/modules/data.js"></script>
        <script src="https://code.highcharts.com/modules/drilldown.js"></script>
        <script src="https://highcharts.github.io/export-csv/export-csv.js"></script>

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
                        zoomType: 'x'
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

    <?php
        /*
        // Get GCcollab API data
        $json_raw = file_get_contents('https://api.gctools.ca/gccollab.ashx?d=1');
        $json = json_decode($json_raw, true);

        // Get data ready for Member Organizations Highcharts
        $organizations = array();
        $topOrgs = array();

        foreach( $json as $key => $value ){
            if($value['Org']){
                if($value['Org'] == "Government of Canada"){
                    $regGC = $value['cnt'];
                }else if( $value['cnt'] < 16){
                    $organizations[] = array($value['Org'], $value['cnt']);
                }else{
                    $topOrgs[] = array($value['Org'], $value['cnt']);
                    $regOrg++;
                }
            }

        }
        sort($organizations);
        $display .= "<script>var regGC=" . $regGC . ";var topOrgs=". json_encode($topOrgs) .
            ";var organizations = " . json_encode($organizations) . ";</script>";

        $display .= '<div id="topOrganizations" style="min-width: 310px; min-height: 350px; margin: 0 auto"></div><div id="organizations" style="min-width: 310px; min-height: 2000px; margin: 0 auto"></div>';
        $display .= "<script>$(function () {
                    Highcharts.chart('topOrganizations', {
                        chart: {
                            type: 'bar'
                        },
                        title: {
                            text: 'Top Organizations: ' + topOrgs.length
                        },
                        xAxis: {
                            type: 'category'
                        },
                        yAxis: {
                            title: {
                                text: 'Member Organizations'
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
                            name: 'Top Organizations',
                            colorByPoint: true,
                            data: topOrgs
                        }]
                    });
        });</script>";

        $display .= "<script>$(function () {
            Highcharts.chart('organizations', {
                chart: {
                    type: 'bar'
                },
                title: {
                    text: 'Member Organizations:' + organizations.length
                },
                xAxis: {
                    type: 'category'
                },
                yAxis: {
                    title: {
                        text: 'Organization Registrations'
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
                    name: 'Organization',
                    colorByPoint: true,
                    data: organizations
                }]
            });
        });</script>";
        */
    ?>

        <hr />

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

        <div id="allMembers" style="min-width: 310px; min-height: 350px; margin: 0 auto"></div>

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

        <hr />
        
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

        <div id="federalMembers" style="min-width: 310px; min-height: 950px; margin: 0 auto"></div>

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

        <hr />
        
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

        <div id="provincialMembers" style="min-width: 310px; min-height: 350px; margin: 0 auto"></div>

        <script>
            $(function () {
                var provincialMembers = <?php echo json_encode($provincialMembers); ?>;
                var provincialMembersDrilldown = <?php echo json_encode($provincialMembersDrilldown); ?>;
                Highcharts.chart('provincialMembers', {
                    chart: {
                        type: 'column'
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

        <hr />
        
    <?php
        // Get 'student' member API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=member.stats&type=student&lang=' . get_current_language());
        $json = json_decode($json_raw, true);

        $studentMembers = $studentMembersMinistry = $studentMembersDrilldown = array();
        $studentMembersCount = 0;
        $institutionName = (get_current_language() == "fr") ? array("college" => "Collège", "university" => "Université") : array("college" => "College", "university" => "University");
        foreach( $json['result'] as $key => $value ){
            if($key == 'college' || $key == 'university'){
                $studentMembers[] = array('name' => $institutionName[$key], 'y' => $value['total'], 'drilldown' => $institutionName[$key]);
                $studentMembersMinistry[$institutionName[$key]] += $value['total'];
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

        <div id="studentMembers" style="min-width: 310px; min-height: 350px; margin: 0 auto"></div>

        <script>
            $(function () {
                var studentMembers = <?php echo json_encode($studentMembers); ?>;
                var studentMembersDrilldown = <?php echo json_encode($studentMembersDrilldown); ?>;
                Highcharts.chart('studentMembers', {
                    chart: {
                        type: 'column'
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

        <hr />
        
    <?php
        // Get 'academic' member API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=member.stats&type=academic&lang=' . get_current_language());
        $json = json_decode($json_raw, true);

        $academicMembers = $academicMembersMinistry = $academicMembersDrilldown = array();
        $academicMembersCount = 0;
        $institutionName = (get_current_language() == "fr") ? array("college" => "Collège", "university" => "Université") : array("college" => "College", "university" => "University");
        foreach( $json['result'] as $key => $value ){
            if($key == 'college' || $key == 'university'){
                $academicMembers[] = array('name' => $institutionName[$key], 'y' => $value['total'], 'drilldown' => $institutionName[$key]);
                $academicMembersMinistry[$institutionName[$key]] += $value['total'];
                $academicMembersCount += $value['total'];
            }

            $institutionData = array();
            foreach( $value as $school => $count ){
                if($school != 'total') $institutionData[] = array($school, $count);
            }
            sort($institutionData);
            $academicMembersDrilldown[] = array('name' => $institutionName[$key], 'id' => $institutionName[$key], 'data' => $institutionData);
        }
        sort($academicMembers);
        sort($academicMembersDrilldown);
    ?>

        <div id="academicMembers" style="min-width: 310px; min-height: 350px; margin: 0 auto"></div>

        <script>
            $(function () {
                var academicMembers = <?php echo json_encode($academicMembers); ?>;
                var academicMembersDrilldown = <?php echo json_encode($academicMembersDrilldown); ?>;
                Highcharts.chart('academicMembers', {
                    chart: {
                        type: 'column'
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

    <?php

        $display = ob_get_clean();

        // Use code below to see which profiles are missing data (aka show up as 'Unknown')
        /*
        ini_set("memory_limit", -1);
        $all_users = elgg_get_entities(array(
            'type' => 'user',
            'limit' => 0
        ));

        $count = $unknown = 0;
        $display .= "<ul>";
        foreach($all_users as $key => $obj){
            if($obj->user_type != "federal" && $obj->user_type != "provincial" && $obj->user_type != "student" && $obj->user_type != "academic"){
                $display .= "<li><a target='_blank' href='https://gccollab.ca/profile/" . $obj->username . "'>" . $obj->username . "</a></li>";
                $unknown++;
            } else {
                $count++;
            }
        }
        $display .= "</ul>";
        $display .= "<p>Count: " . $count . "</p>";
        $display .= "<p>Unknown: " . $unknown . "</p>";
        */

        return $display;
    }