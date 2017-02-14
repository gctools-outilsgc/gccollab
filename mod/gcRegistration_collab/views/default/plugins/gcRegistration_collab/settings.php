<style type="text/css">
	table.fed-table      { border-right:1px solid #ccc; border-bottom:1px solid #ccc; }
	table.fed-table th   { background:#eee; padding:5px; border-left:1px solid #ccc; border-top:1px solid #ccc; }
	table.fed-table td   { padding:5px; border-left:1px solid #ccc; border-top:1px solid #ccc; }
	.save-button		{ margin-top: 10px; }
	.dept_en, .dept_fr 	{ font-size: 14px; width: 100%; }
	input:disabled 		{ background: #ddd; }
	.edit-message 		{ font-weight: bold; }
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
	});
</script>

<div>

<?php

echo '<br />';
echo "<table width='100%' cellpadding='0' cellspacing='0' class='fed-table'>";
echo '<tr> <th>'.elgg_echo('add').'</th> </tr>';
echo '<tr><td>';
echo elgg_echo('gcRegister:occupation:federal').' (EN): '.elgg_view('input/text', array('id' => 'add_federal_en')).'<br/>';
echo elgg_echo('gcRegister:occupation:federal').' (FR): '.elgg_view('input/text', array('id' => 'add_federal_fr')).'<br/>';
echo "<a class='add-federal elgg-button elgg-button-submit btn btn-primary mtm' href='#'>".elgg_echo('add')."</a></td></tr>";
echo "</table><br />";

$deptObj = elgg_get_entities(array(
   	'type' => 'object',
   	'subtype' => 'federal_departments',
));
$departments = get_entity($deptObj[0]->guid);
$depts_en = json_decode($departments->federal_departments_en, true);
$depts_fr = json_decode($departments->federal_departments_fr, true);

if (count($depts_en) > 0) {
	echo "<table width='100%' cellpadding='0' cellspacing='0' class='fed-table'>";
	echo '<thead><tr> <th width="10%"></th> <th width="40%">'.elgg_echo('gcRegister:occupation:federal').' (EN)</th> <th width="40%">'.elgg_echo('gcRegister:occupation:federal').' (FR)</th> <th width="10%"></th> </tr></thead><tbody>';
	foreach ($depts_en as $key => $dept) {
		$delete_btn = elgg_view('output/confirmlink', array(
			'text' => elgg_echo('delete'),
			'href' => "action/gcRegistration_collab/delete?id=" . $key));

		echo "<tr>"; 
		echo "<td> {$delete_btn} </td>";
		echo '<td> <input class="dept_en" data-id="'.$key.'" type="text" value="'.$dept.'" disabled /> </td>';
		echo '<td> <input class="dept_fr" data-id="'.$key.'" type="text" value="'.$depts_fr[$key].'" disabled /> </td>';
		echo "<td> <a class='edit-federal' data-id='".$key."' href='#'>".elgg_echo('edit')."</a> <a class='cancel-federal hidden elgg-button only-one-click elgg-button-cancel btn btn-default' data-id='".$key."' href='#'>".elgg_echo('cancel')."</a> <a class='edit-federal hidden elgg-button only-one-click elgg-button-submit btn btn-primary' data-id='".$key."' href='#'>".elgg_echo('save')."</a> <br> <span class='edit-message' data-id='".$key."'></span> </td>";
		echo "</tr>";
	}
	echo "</tbody></table>";
}

?>

</div>