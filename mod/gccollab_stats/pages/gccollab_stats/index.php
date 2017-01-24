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

        return $display;
    }