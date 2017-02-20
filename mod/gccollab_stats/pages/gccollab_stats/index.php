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
        <script>var lang = '<?php echo get_current_language(); ?>';</script>
        <style>
        @media (max-width: 480px) { 
            .nav-tabs > li {
                float:none;
            }
        }
        </style>
    
        <div id="registrations" style="width: 100%; height: 400px; margin: 0 auto"></div>

    <?php if(get_current_language() == "fr"): ?>
        <script>
            Highcharts.setOptions({
                lang: {
                    months: ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'],
                    weekdays: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
                    shortMonths: ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aout', 'Sept', 'Oct', 'Nov', 'Déc'],
                    decimalPoint: ',',
                    downloadPNG: 'Télécharger en image PNG',
                    downloadJPEG: 'Télécharger en image JPEG',
                    downloadPDF: 'Télécharger en document PDF',
                    downloadSVG: 'Télécharger en document Vectoriel',
                    exportButtonTitle: 'Export du graphique',
                    loading: 'Chargement en cours...',
                    printButtonTitle: 'Imprimer le graphique',
                    resetZoom: 'Réinitialiser le zoom',
                    resetZoomTitle: 'Réinitialiser le zoom au niveau 1:1',
                    thousandsSep: ' ',
                    decimalPoint: ',',
                    printChart: 'Imprimer le graphique',
                    downloadCSV: 'Télécharger en CSV',
                    downloadXLS: 'Télécharger en XLS',
                    viewData: 'Afficher la table des données'
                }
            });
        </script>
    <?php endif; ?>

        <script>
            $(function () {
                Date.prototype.niceDate = function() {
                    if(lang == "fr"){
                        var months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
                        var mm = this.getMonth();
                        var dd = this.getDate();
                        var yy = this.getFullYear();
                        return dd + ' ' + months[mm] + ' ' + yy;
                    } else {
                        var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                        var mm = this.getMonth();
                        var dd = this.getDate();
                        var yy = this.getFullYear();
                        return months[mm] + ' ' + dd + ', ' + yy;
                    }
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
                        text: document.ontouchstart === undefined ? '<?php echo elgg_echo("gccollab_stats:zoommessage"); ?>' : '<?php echo elgg_echo("gccollab_stats:pinchmessage"); ?>'
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
                            return '<b><?php echo elgg_echo("gccollab_stats:date"); ?></b> ' + new Date(registrations[this.series.data.indexOf(this.point)][0]).niceDate()
                            	+ '<br /><b><?php echo elgg_echo("gccollab_stats:signups"); ?></b> ' + registrations[this.series.data.indexOf(this.point)][2]
                            	+ '<br /><b><?php echo elgg_echo("gccollab_stats:total"); ?></b> ' + registrations[this.series.data.indexOf(this.point)][1];
                        }
                    },
                    series: [{
                        type: 'area',
                        name: '<?php echo elgg_echo("gccollab_stats:membercount"); ?>',
                        data: registrations
                    }]
                });
            });
        </script>

        <hr />

    <ul id="member-stats-nav" class="nav nav-tabs">
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
        function compare_count($a, $b){
            return ($b[1] - $a[1]);
        }

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
        usort($allMembers, "compare_count");
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
                        pointFormat: '<span style=\"color:{point.color}\">{point.name}</span>: <b>{point.y}</b> <?php echo elgg_echo("gccollab_stats:users"); ?><br/>'
                    },
                    series: [{
                        name: '<?php echo elgg_echo("gccollab_stats:membertype"); ?>',
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
                        pointFormat: '<span style=\"color:{point.color}\">{point.name}</span>: <b>{point.y}</b> <?php echo elgg_echo("gccollab_stats:users"); ?><br/>'
                    },
                    series: [{
                        name: '<?php echo elgg_echo("gccollab_stats:department"); ?>',
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
                        text: lang == "fr" ? 'Cliquez sur les colonnes pour afficher les ministères de la province ou du territoire' : 'Click the columns to view the ministries within the province/territory'
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
                        pointFormat: '<span style=\"color:{point.color}\">{point.name}</span>: <b>{point.y}</b> <?php echo elgg_echo("gccollab_stats:users"); ?><br/>'
                    },
                    series: [{
                        name: '<?php echo elgg_echo("gccollab_stats:department"); ?>',
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
                        text: lang == "fr" ? 'Cliquez sur les colonnes pour afficher les différentes écoles' : 'Click the columns to view the various schools'
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
                        pointFormat: '<span style=\"color:{point.color}\">{point.name}</span>: <b>{point.y}</b> <?php echo elgg_echo("gccollab_stats:users"); ?><br/>'
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
                        text: lang == "fr" ? 'Cliquez sur les colonnes pour afficher les différentes écoles' : 'Click the columns to view the various schools'
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
                        pointFormat: '<span style=\"color:{point.color}\">{point.name}</span>: <b>{point.y}</b> <?php echo elgg_echo("gccollab_stats:users"); ?><br/>'
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
                        pointFormat: '<span style=\"color:{point.color}\">{point.name}</span>: <b>{point.y}</b> <?php echo elgg_echo("gccollab_stats:users"); ?><br/>'
                    },
                    series: [{
                        name: '<?php echo elgg_echo("gccollab_stats:other"); ?>',
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
            $("#member-stats-nav li a").click(function() {
                setTimeout(function(){
                    $("#registrations").highcharts().reflow();
                    $("#allMembers").highcharts().reflow();
                    $("#federalMembers").highcharts().reflow();
                    $("#provincialMembers").highcharts().reflow();
                    $("#studentMembers").highcharts().reflow();
                    $("#academicMembers").highcharts().reflow();
                    $("#otherMembers").highcharts().reflow();
                }, 5);
            });
        });
    </script>

<?php if( elgg_is_admin_logged_in() ): ?>
    
    <hr />

    <ul id="site-stats-nav" class="nav nav-tabs">
        <li role="presentation" class="active"><a href="#wireposts" aria-controls="wireposts" role="tab" data-toggle="tab"><?php echo elgg_echo("gccollab_stats:wireposts:title"); ?></a></li>
        <li role="presentation"><a href="#blogposts" aria-controls="blogposts" role="tab" data-toggle="tab"><?php echo elgg_echo("gccollab_stats:blogposts:title"); ?></a></li>
        <li role="presentation"><a href="#comments" aria-controls="comments" role="tab" data-toggle="tab"><?php echo elgg_echo("gccollab_stats:comments:title"); ?></a></li>
        <li role="presentation"><a href="#groupscreated" aria-controls="groupscreated" role="tab" data-toggle="tab"><?php echo elgg_echo("gccollab_stats:groupscreated:title"); ?></a></li>
        <?php /* <li role="presentation"><a href="#groupsjoined" aria-controls="groupsjoined" role="tab" data-toggle="tab"><?php echo elgg_echo("gccollab_stats:groupsjoined:title"); ?></a></li> */ ?>
        <li role="presentation"><a href="#likes" aria-controls="likes" role="tab" data-toggle="tab"><?php echo elgg_echo("gccollab_stats:likes:title"); ?></a></li>
        <li role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab"><?php echo elgg_echo("gccollab_stats:messages:title"); ?></a></li>
    </ul>

    <div class="tab-content" style="width: 100%; max-width:100%;">
        <div role="tabpanel" class="tab-pane active" id="wireposts">

    <?php
        // Get Wire Posts API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=site.stats&type=wireposts&lang=' . get_current_language());
        $json = json_decode($json_raw, true);

        $dates = $wireposts = array();
        foreach( $json['result'] as $key => $value ){
            $date = date("F j, Y", $value[0]);
            $dates[strtotime($date)]++;
        }
        foreach( $dates as $key => $value ){
            $wireposts[] = array($key * 1000, $value, date("F j, Y", $key));
        }
        sort($wireposts);
    ?>

        <div id="wirepostsChart" style="width: 100%; max-width:100%; min-height: 350px; margin: 0 auto"></div>

        <script>
            $(function () {
                var wireposts = <?php echo json_encode($wireposts); ?>;
                Highcharts.chart('wirepostsChart', {
                    chart: {
                        zoomType: 'x'
                    },
                    title: {
                        text: '<?php echo elgg_echo("gccollab_stats:wireposts:title"); ?>'
                    },
                    subtitle: {
                        text: document.ontouchstart === undefined ? '<?php echo elgg_echo("gccollab_stats:zoommessage"); ?>' : '<?php echo elgg_echo("gccollab_stats:pinchmessage"); ?>'
                    },
                    xAxis: {
                        type: 'datetime'
                    },
                    yAxis: {
                        title: {
                            text: '<?php echo elgg_echo("gccollab_stats:wireposts:amount"); ?>'
                        }
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
                            return '<b><?php echo elgg_echo("gccollab_stats:date"); ?></b> ' + new Date(wireposts[this.series.data.indexOf(this.point)][2]).niceDate() + '<br /><b><?php echo elgg_echo("gccollab_stats:total"); ?></b> ' + wireposts[this.series.data.indexOf(this.point)][1];
                        }
                    },
                    series: [{
                        type: 'area',
                        name: '<?php echo elgg_echo("gccollab_stats:wireposts:title"); ?>',
                        data: wireposts
                    }]
                });
            });
        </script>
        </div>

        <div role="tabpanel" class="tab-pane" id="blogposts">
        
    <?php
        // Get Blog Posts API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=site.stats&type=blogposts&lang=' . get_current_language());
        $json = json_decode($json_raw, true);

        $dates = $blogposts = array();
        foreach( $json['result'] as $key => $value ){
            $date = date("F j, Y", $value[0]);
            $dates[strtotime($date)]++;
        }
        foreach( $dates as $key => $value ){
            $blogposts[] = array($key * 1000, $value, date("F j, Y", $key));
        }
        sort($blogposts);
    ?>

        <div id="blogpostsChart" style="width: 100%; max-width:100%; min-height: 350px; margin: 0 auto"></div>

        <script>
            $(function () {
                var blogposts = <?php echo json_encode($blogposts); ?>;
                Highcharts.chart('blogpostsChart', {
                    chart: {
                        zoomType: 'x'
                    },
                    title: {
                        text: '<?php echo elgg_echo("gccollab_stats:blogposts:title"); ?>'
                    },
                    subtitle: {
                        text: document.ontouchstart === undefined ? '<?php echo elgg_echo("gccollab_stats:zoommessage"); ?>' : '<?php echo elgg_echo("gccollab_stats:pinchmessage"); ?>'
                    },
                    xAxis: {
                        type: 'datetime'
                    },
                    yAxis: {
                        title: {
                            text: '<?php echo elgg_echo("gccollab_stats:blogposts:amount"); ?>'
                        }
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
                            return '<b><?php echo elgg_echo("gccollab_stats:date"); ?></b> ' + new Date(blogposts[this.series.data.indexOf(this.point)][2]).niceDate() + '<br /><b><?php echo elgg_echo("gccollab_stats:total"); ?></b> ' + blogposts[this.series.data.indexOf(this.point)][1];
                        }
                    },
                    series: [{
                        type: 'area',
                        name: '<?php echo elgg_echo("gccollab_stats:blogposts:title"); ?>',
                        data: blogposts
                    }]
                });
            });
        </script>
        </div>

        <div role="tabpanel" class="tab-pane" id="comments">

    <?php
        // Get Comments API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=site.stats&type=comments&lang=' . get_current_language());
        $json = json_decode($json_raw, true);

        $dates = $comments = array();
        foreach( $json['result'] as $key => $value ){
            $date = date("F j, Y", $value[0]);
            $dates[strtotime($date)]++;
        }
        foreach( $dates as $key => $value ){
            $comments[] = array($key * 1000, $value, date("F j, Y", $key));
        }
        sort($comments);
    ?>

        <div id="commentsChart" style="width: 100%; max-width:100%; min-height: 350px; margin: 0 auto"></div>

        <script>
            $(function () {
                var comments = <?php echo json_encode($comments); ?>;
                Highcharts.chart('commentsChart', {
                    chart: {
                        zoomType: 'x'
                    },
                    title: {
                        text: '<?php echo elgg_echo("gccollab_stats:comments:title"); ?>'
                    },
                    subtitle: {
                        text: document.ontouchstart === undefined ? '<?php echo elgg_echo("gccollab_stats:zoommessage"); ?>' : '<?php echo elgg_echo("gccollab_stats:pinchmessage"); ?>'
                    },
                    xAxis: {
                        type: 'datetime'
                    },
                    yAxis: {
                        title: {
                            text: '<?php echo elgg_echo("gccollab_stats:comments:amount"); ?>'
                        }
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
                            return '<b><?php echo elgg_echo("gccollab_stats:date"); ?></b> ' + new Date(comments[this.series.data.indexOf(this.point)][2]).niceDate() + '<br /><b><?php echo elgg_echo("gccollab_stats:total"); ?></b> ' + comments[this.series.data.indexOf(this.point)][1];
                        }
                    },
                    series: [{
                        type: 'area',
                        name: '<?php echo elgg_echo("gccollab_stats:comments:title"); ?>',
                        data: comments
                    }]
                });
            });
        </script>
        </div>

        <div role="tabpanel" class="tab-pane" id="groupscreated">

    <?php
        // Get Groups Created API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=site.stats&type=groupscreated&lang=' . get_current_language());
        $json = json_decode($json_raw, true);

        $dates = $groupscreated = array();
        foreach( $json['result'] as $key => $value ){
            $date = date("F j, Y", $value[0]);
            $dates[strtotime($date)]['count']++;
            if($dates[strtotime($date)]['names']){
                $dates[strtotime($date)]['names'] = $dates[strtotime($date)]['names'] . "<br />" . $value[1];;
            } else {
                $dates[strtotime($date)]['names'] = $value[1];
            }
        }
        foreach( $dates as $key => $value ){
            $groupscreated[] = array($key * 1000, $value['count'], date("F j, Y", $key), $value['names']);
        }
        sort($groupscreated);
    ?>

        <div id="groupscreatedChart" style="width: 100%; max-width:100%; min-height: 350px; margin: 0 auto"></div>

        <script>
            $(function () {
                var groupscreated = <?php echo json_encode($groupscreated); ?>;
                Highcharts.chart('groupscreatedChart', {
                    chart: {
                        zoomType: 'x'
                    },
                    title: {
                        text: '<?php echo elgg_echo("gccollab_stats:groupscreated:title"); ?>'
                    },
                    subtitle: {
                        text: document.ontouchstart === undefined ? '<?php echo elgg_echo("gccollab_stats:zoommessage"); ?>' : '<?php echo elgg_echo("gccollab_stats:pinchmessage"); ?>'
                    },
                    xAxis: {
                        type: 'datetime'
                    },
                    yAxis: {
                        title: {
                            text: '<?php echo elgg_echo("gccollab_stats:groupscreated:amount"); ?>'
                        }
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
                            return '<b><?php echo elgg_echo("gccollab_stats:groups:label"); ?></b> ' + groupscreated[this.series.data.indexOf(this.point)][3] + '<br /><b><?php echo elgg_echo("gccollab_stats:date"); ?></b> ' + new Date(groupscreated[this.series.data.indexOf(this.point)][2]).niceDate() + '<br /><b><?php echo elgg_echo("gccollab_stats:total"); ?></b> ' + groupscreated[this.series.data.indexOf(this.point)][1];
                        }
                    },
                    series: [{
                        type: 'area',
                        name: '<?php echo elgg_echo("gccollab_stats:groupscreated:title"); ?>',
                        data: groupscreated
                    }]
                });
            });
        </script>
        </div>

    <?php /*

        <div role="tabpanel" class="tab-pane" id="groupsjoined">

    <?php
        // Get Groups Joined API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=site.stats&type=groupsjoined&lang=' . get_current_language());
        $json = json_decode($json_raw, true);

        $dates = $groupsjoined = array();
        foreach( $json['result'] as $key => $value ){
            $date = date("F j, Y", $value[0]);
            $dates[strtotime($date)]++;
        }
        foreach( $dates as $key => $value ){
            $groupsjoined[] = array($key * 1000, $value, date("F j, Y", $key));
        }
        sort($groupsjoined);
    ?>

        <div id="groupsjoinedChart" style="width: 100%; max-width:100%; min-height: 350px; margin: 0 auto"></div>

        <script>
            $(function () {
                var groupsjoined = <?php echo json_encode($groupsjoined); ?>;
                Highcharts.chart('groupsjoinedChart', {
                    chart: {
                        zoomType: 'x'
                    },
                    title: {
                        text: '<?php echo elgg_echo("gccollab_stats:groupsjoined:title"); ?>'
                    },
                    subtitle: {
                        text: document.ontouchstart === undefined ? '<?php echo elgg_echo("gccollab_stats:zoommessage"); ?>' : '<?php echo elgg_echo("gccollab_stats:pinchmessage"); ?>'
                    },
                    xAxis: {
                        type: 'datetime'
                    },
                    yAxis: {
                        title: {
                            text: '<?php echo elgg_echo("gccollab_stats:groupsjoined:amount"); ?>'
                        }
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
                            return '<b><?php echo elgg_echo("gccollab_stats:date"); ?></b> ' + new Date(groupsjoined[this.series.data.indexOf(this.point)][2]).niceDate() + '<br /><b><?php echo elgg_echo("gccollab_stats:total"); ?></b> ' + groupsjoined[this.series.data.indexOf(this.point)][1];
                        }
                    },
                    series: [{
                        type: 'area',
                        name: '<?php echo elgg_echo("gccollab_stats:groupsjoined:title"); ?>',
                        data: groupsjoined
                    }]
                });
            });
        </script>
        </div>

    */ ?>

        <div role="tabpanel" class="tab-pane" id="likes">

    <?php
        // Get Likes API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=site.stats&type=likes&lang=' . get_current_language());
        $json = json_decode($json_raw, true);

        $dates = $likes = array();
        foreach( $json['result'] as $key => $value ){
            $date = date("F j, Y", $value[0]);
            $dates[strtotime($date)]++;
        }
        foreach( $dates as $key => $value ){
            $likes[] = array($key * 1000, $value, date("F j, Y", $key));
        }
        sort($likes);
    ?>

        <div id="likesChart" style="width: 100%; max-width:100%; min-height: 350px; margin: 0 auto"></div>

        <script>
            $(function () {
                var likes = <?php echo json_encode($likes); ?>;
                Highcharts.chart('likesChart', {
                    chart: {
                        zoomType: 'x'
                    },
                    title: {
                        text: '<?php echo elgg_echo("gccollab_stats:likes:title"); ?>'
                    },
                    subtitle: {
                        text: document.ontouchstart === undefined ? '<?php echo elgg_echo("gccollab_stats:zoommessage"); ?>' : '<?php echo elgg_echo("gccollab_stats:pinchmessage"); ?>'
                    },
                    xAxis: {
                        type: 'datetime'
                    },
                    yAxis: {
                        title: {
                            text: '<?php echo elgg_echo("gccollab_stats:likes:amount"); ?>'
                        }
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
                            return '<b><?php echo elgg_echo("gccollab_stats:date"); ?></b> ' + new Date(likes[this.series.data.indexOf(this.point)][2]).niceDate() + '<br /><b><?php echo elgg_echo("gccollab_stats:total"); ?></b> ' + likes[this.series.data.indexOf(this.point)][1];
                        }
                    },
                    series: [{
                        type: 'area',
                        name: '<?php echo elgg_echo("gccollab_stats:likes:title"); ?>',
                        data: likes
                    }]
                });
            });
        </script>
        </div>

        <div role="tabpanel" class="tab-pane" id="messages">

    <?php
        // Get Messages API data
        $json_raw = file_get_contents(elgg_get_site_url() . 'services/api/rest/json/?method=site.stats&type=messages&lang=' . get_current_language());
        $json = json_decode($json_raw, true);

        $dates = $messages = array();
        foreach( $json['result'] as $key => $value ){
            $date = date("F j, Y", $value[0]);
            $dates[strtotime($date)]++;
        }
        foreach( $dates as $key => $value ){
            $messages[] = array($key * 1000, $value, date("F j, Y", $key));
        }
        sort($messages);
    ?>

        <div id="messagesChart" style="width: 100%; max-width:100%; min-height: 350px; margin: 0 auto"></div>

        <script>
            $(function () {
                var messages = <?php echo json_encode($messages); ?>;
                Highcharts.chart('messagesChart', {
                    chart: {
                        zoomType: 'x'
                    },
                    title: {
                        text: '<?php echo elgg_echo("gccollab_stats:messages:title"); ?>'
                    },
                    subtitle: {
                        text: document.ontouchstart === undefined ? '<?php echo elgg_echo("gccollab_stats:zoommessage"); ?>' : '<?php echo elgg_echo("gccollab_stats:pinchmessage"); ?>'
                    },
                    xAxis: {
                        type: 'datetime'
                    },
                    yAxis: {
                        title: {
                            text: '<?php echo elgg_echo("gccollab_stats:messages:amount"); ?>'
                        }
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
                            return '<b><?php echo elgg_echo("gccollab_stats:date"); ?></b> ' + new Date(messages[this.series.data.indexOf(this.point)][2]).niceDate() + '<br /><b><?php echo elgg_echo("gccollab_stats:total"); ?></b> ' + messages[this.series.data.indexOf(this.point)][1];
                        }
                    },
                    series: [{
                        type: 'area',
                        name: '<?php echo elgg_echo("gccollab_stats:messages:title"); ?>',
                        data: messages
                    }]
                });
            });
        </script>
        </div>

    </div>

    <script>
        $(function () {
            $("#site-stats-nav li a").click(function() {
                setTimeout(function(){
                    $("#wirepostsChart").highcharts().reflow();
                    $("#blogpostsChart").highcharts().reflow();
                    $("#commentsChart").highcharts().reflow();
                    $("#groupscreatedChart").highcharts().reflow();
                    <?php /* $("#groupsjoinedChart").highcharts().reflow(); */ ?>
                    $("#likesChart").highcharts().reflow();
                    $("#messagesChart").highcharts().reflow();
                }, 5);
            });
        });
    </script>

<?php endif; ?>

    <?php

        $display = ob_get_clean();

        return $display;
    }