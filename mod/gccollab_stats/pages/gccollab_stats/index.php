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

        $count = 0;
        $regGC = 0;
        $regOrg = 0;

        // Get data ready for Member Registration Highcharts
        $registrations = array();
        foreach( $json as $key => $value ){
            if( $value['RegisteredSmall'] ){
            $count += $value['cnt'];
                $registrations[] = array(strtotime($value['RegisteredSmall']) * 1000, $count, $value['cnt']);
            }
        }
        usort($registrations, "compare_func");
        $display = "<script>var count=" . $count . ";var registrations = " . json_encode($registrations) . ";</script>";

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

        $display .= '<script src="https://code.highcharts.com/highcharts.js"></script>
            <script src="https://code.highcharts.com/modules/exporting.js"></script>
            <div id="registrations" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
			<div id="topOrganizations" style="min-width: 310px; min-height: 350px; margin: 0 auto"></div>
            <div id="organizations" style="min-width: 310px; min-height: 2000px; margin: 0 auto"></div>';

        $display .= "<script>$(function () {
            Date.prototype.niceDate = function() {
                var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                var mm = this.getMonth();
                var dd = this.getDate();
                var yy = this.getFullYear();
                return months[mm] + ' ' + dd + ', ' + yy;
            };
            Highcharts.chart('registrations', {
                chart: {
                    zoomType: 'x'
                },
                title: {
                    text: 'Registered Members:' + count + ' (Government of Canada: ' + regGC + ')'
                },
                subtitle: {
                    text: document.ontouchstart === undefined ? 'Click and drag in the plot area to zoom in' : 'Pinch the chart to zoom in'
                },
                xAxis: {
                    type: 'datetime'
                },
                yAxis: {
                    title: {
                        text: '# of members'
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
        });</script>";

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


        /****** CHARTS USING NEW API ******/
        
        $display .= "<hr />";

        // Get 'all' member API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=member.stats&type=all');
        $json = json_decode($json_raw, true);

        $allMembers = array();
        $allMembersCount = 0;
        foreach( $json['result'] as $key => $value ){
            $allMembers[] = array(ucfirst($key), $value);
            $allMembersCount += $value;
        }
        sort($allMembers);

        $display .= "<script>var allMembers=" . json_encode($allMembers) . ";</script>";
        $display .= '<div id="allMembers" style="min-width: 310px; min-height: 350px; margin: 0 auto"></div>';
        $display .= "<script>$(function () {
                    Highcharts.chart('allMembers', {
                        chart: {
                            type: 'bar'
                        },
                        title: {
                            text: 'Member Types (" . $allMembersCount . " total)'
                        },
                        xAxis: {
                            type: 'category'
                        },
                        yAxis: {
                            title: {
                                text: '# of members'
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
        });</script>";


        // Get 'federal' member API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=member.stats&type=federal');
        $json = json_decode($json_raw, true);

        $federalMembers = array();
        $federalMembersCount = 0;
        foreach( $json['result'] as $key => $value ){
            $federalMembers[] = array(ucfirst($key), $value);
            $federalMembersCount += $value;
        }
        sort($federalMembers);

        $display .= "<script>var federalMembers=" . json_encode($federalMembers) . ";</script>";
        $display .= '<div id="federalMembers" style="min-width: 310px; min-height: 350px; margin: 0 auto"></div>';
        $display .= "<script>$(function () {
                    Highcharts.chart('federalMembers', {
                        chart: {
                            type: 'bar'
                        },
                        title: {
                            text: 'Federal Members (" . $federalMembersCount . " total)'
                        },
                        xAxis: {
                            type: 'category'
                        },
                        yAxis: {
                            title: {
                                text: '# of members'
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
        });</script>";


        // Get 'provincial' member API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=member.stats&type=provincial');
        $json = json_decode($json_raw, true);

        $provincialMembers = $provincialMembersMinistry = $provincialMembersDrilldown = array();
        $provincialMembersCount = 0;
        foreach( $json['result'] as $key => $value ){
            $provincialMembers[] = array('name' => $key, 'y' => $value['total'], 'drilldown' => $key);
            $provincialMembersMinistry[$key] += $value['total'];
            $provincialMembersCount += $value['total'];

            $provinceData = array();
            foreach( $value as $ministry => $count ){
                if($ministry != 'total') $provinceData[] = array($ministry, $count);
            }
            $provincialMembersDrilldown[] = array('name' => $key, 'id' => $key, 'data' => $provinceData);
        }
        sort($provincialMembers);
        sort($provincialMembersDrilldown);

        $display .= '<script src="https://code.highcharts.com/modules/data.js"></script>
            <script src="https://code.highcharts.com/modules/drilldown.js"></script>';
        $display .= "<script>var provincialMembers=" . json_encode($provincialMembers) . "; var provincialMembersDrilldown=" . json_encode($provincialMembersDrilldown) . ";</script>";
        $display .= '<div id="provincialMembers" style="min-width: 310px; min-height: 350px; margin: 0 auto"></div>';
        $display .= "<script>$(function () {
                    Highcharts.chart('provincialMembers', {
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: 'Provincial Members (" . $provincialMembersCount . " total)'
                        },
                        subtitle: {
                            text: 'Click the columns to view the ministries within the province/territory.'
                        },
                        xAxis: {
                            type: 'category'
                        },
                        yAxis: {
                            title: {
                                text: '# of members'
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
        });</script>";


        // Get 'student' member API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=member.stats&type=student');
        $json = json_decode($json_raw, true);

        $studentMembers = $studentMembersMinistry = $studentMembersDrilldown = array();
        $studentMembersCount = 0;
        foreach( $json['result'] as $key => $value ){
            $studentMembers[] = array('name' => ucfirst($key), 'y' => $value['total'], 'drilldown' => ucfirst($key));
            $studentMembersMinistry[ucfirst($key)] += $value['total'];
            $studentMembersCount += $value['total'];

            $institutionData = array();
            foreach( $value as $school => $count ){
                if($school != 'total') $institutionData[] = array($school, $count);
            }
            $studentMembersDrilldown[] = array('name' => ucfirst($key), 'id' => ucfirst($key), 'data' => $institutionData);
        }
        sort($studentMembers);
        sort($studentMembersDrilldown);

        $display .= '<script src="https://code.highcharts.com/modules/data.js"></script>
            <script src="https://code.highcharts.com/modules/drilldown.js"></script>';
        $display .= "<script>var studentMembers=" . json_encode($studentMembers) . "; var studentMembersDrilldown=" . json_encode($studentMembersDrilldown) . ";</script>";
        $display .= '<div id="studentMembers" style="min-width: 310px; min-height: 350px; margin: 0 auto"></div>';
        $display .= "<script>$(function () {
                    Highcharts.chart('studentMembers', {
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: 'Student Members (" . $studentMembersCount . " total)'
                        },
                        subtitle: {
                            text: 'Click the columns to view the various schools.'
                        },
                        xAxis: {
                            type: 'category'
                        },
                        yAxis: {
                            title: {
                                text: '# of members'
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
                        }
                    });
        });</script>";


        // Get 'academic' member API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=member.stats&type=academic');
        $json = json_decode($json_raw, true);

        $academicMembers = $academicMembersMinistry = $academicMembersDrilldown = array();
        $academicMembersCount = 0;
        foreach( $json['result'] as $key => $value ){
            $academicMembers[] = array('name' => ucfirst($key), 'y' => $value['total'], 'drilldown' => ucfirst($key));
            $academicMembersMinistry[ucfirst($key)] += $value['total'];
            $academicMembersCount += $value['total'];

            $institutionData = array();
            foreach( $value as $school => $count ){
                if($school != 'total') $institutionData[] = array($school, $count);
            }
            $academicMembersDrilldown[] = array('name' => ucfirst($key), 'id' => ucfirst($key), 'data' => $institutionData);
        }
        sort($academicMembers);
        sort($academicMembersDrilldown);

        $display .= '<script src="https://code.highcharts.com/modules/data.js"></script>
            <script src="https://code.highcharts.com/modules/drilldown.js"></script>';
        $display .= "<script>var academicMembers=" . json_encode($academicMembers) . "; var academicMembersDrilldown=" . json_encode($academicMembersDrilldown) . ";</script>";
        $display .= '<div id="academicMembers" style="min-width: 310px; min-height: 350px; margin: 0 auto"></div>';
        $display .= "<script>$(function () {
                    Highcharts.chart('academicMembers', {
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: 'Academic Members (" . $academicMembersCount . " total)'
                        },
                        subtitle: {
                            text: 'Click the columns to view the various schools.'
                        },
                        xAxis: {
                            type: 'category'
                        },
                        yAxis: {
                            title: {
                                text: '# of members'
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
                        }
                    });
        });</script>";

        return $display;
    }