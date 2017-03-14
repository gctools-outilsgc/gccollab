<style type="text/css">
	table.depts     	{ border-right:1px solid #ccc; border-bottom:1px solid #ccc; margin-top: 10px; }
	table.depts th 		{ background:#eee; padding:5px; border-left:1px solid #ccc; border-top:1px solid #ccc; }
	table.depts td 		{ padding:5px; border-left:1px solid #ccc; border-top:1px solid #ccc; }
	.save-button		{ margin-top: 10px; }
	.dept_en, .dept_fr 	{ font-size: 14px; width: 100%; }
	input:disabled 		{ background: #ddd; }
	.edit-message 		{ font-weight: bold; }
	.elgg-tabs ul 		{ display: inline-block; }
</style>

<script type="text/javascript">
	$(function() {
		function toggleButtons(key){
			$('input[data-id="' + key + '"]').prop("disabled", function(i, v){ return !v; });
		    $('a.edit-federal[data-id="' + key + '"]').toggleClass('hidden');
		    $('a.cancel-federal[data-id="' + key + '"]').toggleClass('hidden');
		    $('a.edit-federal[data-id="' + key + '"]').toggleClass('hidden');
		}

		function showMessage(key, msg){
			$('span.edit-message[data-id="' + key + '"]').show().text(msg).delay(2000).fadeOut();
		}

		$("a.add-federal").click(function(e){
			e.preventDefault();
		    var dept_en = $("#add_federal_en").val();
		    var dept_fr = $("#add_federal_fr").val();

		    if(dept_en !== "" && dept_fr !== ""){
			    elgg.action('gcRegistration_collab/add', {
				 	data: {
				    	dept_en: dept_en,
				    	dept_fr: dept_fr,
					},
				  	success: function (wrapper) {
					    if (wrapper.output == 1) {
					    	console.log("Saved!");
					    	elgg.system_message('Saved!');
					    	location.reload(true);
					    } else {
					    	console.log("Error!");
					    }
				  	},
				    error: function (jqXHR, textStatus, errorThrown) {
				        console.log("Error: " + errorThrown);
				    }
				});
		    }
		});

		$("a.edit-federal, a.cancel-federal").click(function(e){
			e.preventDefault();
		    var id = $(this).data('id');
		    toggleButtons(id);
		    if($(this).hasClass("edit-federal")){ $('input.dept_en[data-id="' + id + '"]').focus(); }
		});

		$("a.edit-federal").click(function(e){
			e.preventDefault();
		    var dept_en = $('input.dept_en[data-id="' + id + '"]').val();
		    var dept_fr = $('input.dept_fr[data-id="' + id + '"]').val();

		 	elgg.action('gcRegistration_collab/edit', {
			 	data: {
			    	dept_en: dept_en,
			    	dept_fr: dept_fr,
				},
			  	success: function (wrapper) {
				    if (wrapper.output == 1) {
				    	showMessage(id, "Saved!");
				    	toggleButtons(id);
				    } else {
				    	showMessage(id, "Error!");
				    }
			  	},
			    error: function (jqXHR, textStatus, errorThrown) {
			        console.log("Error: " + errorThrown);
				    showMessage(id, "Error!");
			    }
			});
		});

		$( "#tabs" ).tabs();
	});
</script>

<div>

<?php

function deptSort($a, $b){
    return ($a[0] < $b[0]) ? -1 : 1;
}

echo '<br />';
echo '<div id="tabs"><ul class="elgg-tabs">
	<li><a href="#federal">Federal Departments</a></li>
	<li><a href="#provincial">Provincial/Territorial Departments</a></li>
	<li><a href="#universities">Universities</a></li>
	<li><a href="#colleges">Colleges</a></li>
	<li><a href="#other">Other</a></li></ul>';

echo '<div id="federal">';

echo '<table width="100%" cellpadding="0" cellspacing="0" class="depts">';
echo '<tr> <th>'.elgg_echo('add').'</th> </tr>';
echo '<tr><td>';
echo elgg_echo('gcRegister:department').' (EN): '.elgg_view('input/text', array('id' => 'add_federal_en')).'<br/>';
echo elgg_echo('gcRegister:department').' (FR): '.elgg_view('input/text', array('id' => 'add_federal_fr')).'<br/>';
echo '<a class="add-federal elgg-button elgg-button-submit btn btn-primary mtm" href="#">'.elgg_echo('add').'</a></td></tr>';
echo '</table>';

$deptObj = elgg_get_entities(array(
   	'type' => 'object',
   	'subtype' => 'federal_departments',
));
$departments = get_entity($deptObj[0]->guid);
$depts_en = json_decode($departments->federal_departments_en, true);
$depts_fr = json_decode($departments->federal_departments_fr, true);
ksort($depts_en);

if (count($depts_en) > 0) {
	echo '<table width="100%" cellpadding="0" cellspacing="0" class="depts">';
	echo '<thead><tr> <th width="10%"></th> <th width="40%">'.elgg_echo('gcRegister:department').' (EN)</th> <th width="40%">'.elgg_echo('gcRegister:department').' (FR)</th> <th width="10%"></th> </tr></thead><tbody>';
	foreach ($depts_en as $key => $dept) {
		$delete_btn = elgg_view('output/confirmlink', array(
			'text' => elgg_echo('delete'),
			'href' => "action/gcRegistration_collab/delete?id=" . $key));

		echo '<tr>'; 
		echo '<td>'.$delete_btn.'</td>';
		echo '<td> <input class="dept_en" data-id="'.$key.'" type="text" value="'.$dept.'" disabled /> </td>';
		echo '<td> <input class="dept_fr" data-id="'.$key.'" type="text" value="'.$depts_fr[$key].'" disabled /> </td>';
		echo '<td> <a class="edit-federal" data-id="'.$key.'" href="#">'.elgg_echo('edit').'</a> <a class="cancel-federal hidden elgg-button only-one-click elgg-button-cancel btn btn-default" data-id="'.$key.'" href="#">'.elgg_echo('cancel').'</a> <a class="edit-federal hidden elgg-button only-one-click elgg-button-submit btn btn-primary" data-id="'.$key.'" href="#">'.elgg_echo('save').'</a> <br> <span class="edit-message" data-id="'.$key.'"></span> </td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
}

echo '</div>';

echo '<div id="provincial">';

echo '<table width="100%" cellpadding="0" cellspacing="0" class="depts">';
echo '<tr> <th>'.elgg_echo('add').'</th> </tr>';
echo '<tr><td>';
echo elgg_echo('gcRegister:province').' (EN): '.elgg_view('input/text', array('id' => 'add_provincial_en')).'<br/>';
echo elgg_echo('gcRegister:province').' (FR): '.elgg_view('input/text', array('id' => 'add_provincial_fr')).'<br/>';
echo '<a class="add-province elgg-button elgg-button-submit btn btn-primary mtm" href="#">'.elgg_echo('add').'</a></td></tr>';
echo '</table>';

$provObj = elgg_get_entities(array(
    'type' => 'object',
    'subtype' => 'provinces',
));
$provs = get_entity($provObj[0]->guid);
$provs_en = json_decode($provs->provinces_en, true);
$provs_fr = json_decode($provs->provinces_fr, true);
ksort($provs_en);

if (count($provs_en) > 0) {
	echo '<table width="100%" cellpadding="0" cellspacing="0" class="depts">';
	echo '<thead><tr> <th width="10%"></th> <th width="40%">'.elgg_echo('gcRegister:province').' (EN)</th> <th width="40%">'.elgg_echo('gcRegister:province').' (FR)</th> <th width="10%"></th> </tr></thead><tbody>';
	foreach ($provs_en as $key => $prov) {
		$delete_btn = elgg_view('output/confirmlink', array(
			'text' => elgg_echo('delete'),
			'href' => "action/gcRegistration_collab/delete?id=" . $key));

		echo '<tr>'; 
		echo '<td>'.$delete_btn.'</td>';
		echo '<td> <input class="dept_en" data-id="'.$key.'" type="text" value="'.$prov.'" disabled /> </td>';
		echo '<td> <input class="dept_fr" data-id="'.$key.'" type="text" value="'.$provs_fr[$key].'" disabled /> </td>';
		echo '<td> <a class="edit-province" data-id="'.$key.'" href="#">'.elgg_echo('edit').'</a> <a class="cancel-province hidden elgg-button only-one-click elgg-button-cancel btn btn-default" data-id="'.$key.'" href="#">'.elgg_echo('cancel').'</a> <a class="edit-province hidden elgg-button only-one-click elgg-button-submit btn btn-primary" data-id="'.$key.'" href="#">'.elgg_echo('save').'</a> <br> <span class="edit-message" data-id="'.$key.'"></span> </td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
}

echo '</div>';

echo '<div id="universities">';

echo '<table width="100%" cellpadding="0" cellspacing="0" class="depts">';
echo '<tr> <th>'.elgg_echo('add').'</th> </tr>';
echo '<tr><td>';
echo elgg_echo('gcRegister:university').' (EN): '.elgg_view('input/text', array('id' => 'add_provincial_en')).'<br/>';
echo elgg_echo('gcRegister:university').' (FR): '.elgg_view('input/text', array('id' => 'add_provincial_fr')).'<br/>';
echo '<a class="add-university elgg-button elgg-button-submit btn btn-primary mtm" href="#">'.elgg_echo('add').'</a></td></tr>';
echo '</table>';

$uniObj = elgg_get_entities(array(
    'type' => 'object',
    'subtype' => 'universities',
));
$unis = get_entity($uniObj[0]->guid);
$unis_en = json_decode($unis->universities_en, true);
$unis_fr = json_decode($unis->universities_fr, true);
ksort($unis_en);

if (count($unis_en) > 0) {
	echo '<table width="100%" cellpadding="0" cellspacing="0" class="depts">';
	echo '<thead><tr> <th width="10%"></th> <th width="40%">'.elgg_echo('gcRegister:university').' (EN)</th> <th width="40%">'.elgg_echo('gcRegister:university').' (FR)</th> <th width="10%"></th> </tr></thead><tbody>';
	foreach ($unis_en as $key => $uni) {
		$delete_btn = elgg_view('output/confirmlink', array(
			'text' => elgg_echo('delete'),
			'href' => "action/gcRegistration_collab/delete?id=" . $key));

		echo '<tr>'; 
		echo '<td>'.$delete_btn.'</td>';
		echo '<td> <input class="dept_en" data-id="'.$key.'" type="text" value="'.$uni.'" disabled /> </td>';
		echo '<td> <input class="dept_fr" data-id="'.$key.'" type="text" value="'.$unis_fr[$key].'" disabled /> </td>';
		echo '<td> <a class="edit-university" data-id="'.$key.'" href="#">'.elgg_echo('edit').'</a> <a class="cancel-university hidden elgg-button only-one-click elgg-button-cancel btn btn-default" data-id="'.$key.'" href="#">'.elgg_echo('cancel').'</a> <a class="edit-university hidden elgg-button only-one-click elgg-button-submit btn btn-primary" data-id="'.$key.'" href="#">'.elgg_echo('save').'</a> <br> <span class="edit-message" data-id="'.$key.'"></span> </td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
}

echo '</div>';

echo '<div id="colleges">';

echo '<table width="100%" cellpadding="0" cellspacing="0" class="depts">';
echo '<tr> <th>'.elgg_echo('add').'</th> </tr>';
echo '<tr><td>';
echo elgg_echo('gcRegister:college').' (EN): '.elgg_view('input/text', array('id' => 'add_college_en')).'<br/>';
echo elgg_echo('gcRegister:college').' (FR): '.elgg_view('input/text', array('id' => 'add_college_fr')).'<br/>';
echo '<a class="add-college elgg-button elgg-button-submit btn btn-primary mtm" href="#">'.elgg_echo('add').'</a></td></tr>';
echo '</table>';

$colObj = elgg_get_entities(array(
    'type' => 'object',
    'subtype' => 'colleges',
));
$cols = get_entity($colObj[0]->guid);
$cols_en = json_decode($cols->colleges_en, true);
$cols_fr = json_decode($cols->colleges_fr, true);
ksort($cols_en);

if (count($cols_en) > 0) {
	echo '<table width="100%" cellpadding="0" cellspacing="0" class="depts">';
	echo '<thead><tr> <th width="10%"></th> <th width="40%">'.elgg_echo('gcRegister:college').' (EN)</th> <th width="40%">'.elgg_echo('gcRegister:college').' (FR)</th> <th width="10%"></th> </tr></thead><tbody>';
	foreach ($cols_en as $key => $col) {
		$delete_btn = elgg_view('output/confirmlink', array(
			'text' => elgg_echo('delete'),
			'href' => "action/gcRegistration_collab/delete?id=" . $key));

		echo '<tr>'; 
		echo '<td>'.$delete_btn.'</td>';
		echo '<td> <input class="dept_en" data-id="'.$key.'" type="text" value="'.$col.'" disabled /> </td>';
		echo '<td> <input class="dept_fr" data-id="'.$key.'" type="text" value="'.$cols_fr[$key].'" disabled /> </td>';
		echo '<td> <a class="edit-college" data-id="'.$key.'" href="#">'.elgg_echo('edit').'</a> <a class="cancel-college hidden elgg-button only-one-click elgg-button-cancel btn btn-default" data-id="'.$key.'" href="#">'.elgg_echo('cancel').'</a> <a class="edit-college hidden elgg-button only-one-click elgg-button-submit btn btn-primary" data-id="'.$key.'" href="#">'.elgg_echo('save').'</a> <br> <span class="edit-message" data-id="'.$key.'"></span> </td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
}

echo '</div>';

echo '<div id="other">';

echo '<table width="100%" cellpadding="0" cellspacing="0" class="depts">';
echo '<tr> <th>'.elgg_echo('add').'</th> </tr>';
echo '<tr><td>';
echo elgg_echo('gcRegister:occupation:other').' (EN): '.elgg_view('input/text', array('id' => 'add_college_en')).'<br/>';
echo elgg_echo('gcRegister:occupation:other').' (FR): '.elgg_view('input/text', array('id' => 'add_college_fr')).'<br/>';
echo '<a class="add-other elgg-button elgg-button-submit btn btn-primary mtm" href="#">'.elgg_echo('add').'</a></td></tr>';
echo '</table>';

$otherObj = elgg_get_entities(array(
    'type' => 'object',
    'subtype' => 'other',
));
$others = get_entity($otherObj[0]->guid);
$others_en = json_decode($others->other_en, true);
$others_fr = json_decode($others->other_fr, true);
ksort($others_en);

if (count($others_en) > 0) {
	echo '<table width="100%" cellpadding="0" cellspacing="0" class="depts">';
	echo '<thead><tr> <th width="10%"></th> <th width="40%">'.elgg_echo('gcRegister:occupation:other').' (EN)</th> <th width="40%">'.elgg_echo('gcRegister:occupation:other').' (FR)</th> <th width="10%"></th> </tr></thead><tbody>';
	foreach ($others_en as $key => $other) {
		$delete_btn = elgg_view('output/confirmlink', array(
			'text' => elgg_echo('delete'),
			'href' => "action/gcRegistration_collab/delete?id=" . $key));

		echo '<tr>'; 
		echo '<td>'.$delete_btn.'</td>';
		echo '<td> <input class="dept_en" data-id="'.$key.'" type="text" value="'.$other.'" disabled /> </td>';
		echo '<td> <input class="dept_fr" data-id="'.$key.'" type="text" value="'.$others_fr[$key].'" disabled /> </td>';
		echo '<td> <a class="edit-other" data-id="'.$key.'" href="#">'.elgg_echo('edit').'</a> <a class="cancel-other hidden elgg-button only-one-click elgg-button-cancel btn btn-default" data-id="'.$key.'" href="#">'.elgg_echo('cancel').'</a> <a class="edit-other hidden elgg-button only-one-click elgg-button-submit btn btn-primary" data-id="'.$key.'" href="#">'.elgg_echo('save').'</a> <br> <span class="edit-message" data-id="'.$key.'"></span> </td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
}

echo '</div>';

echo '</div>';

?>

</div>