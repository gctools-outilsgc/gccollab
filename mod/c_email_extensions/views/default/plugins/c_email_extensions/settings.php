<style type="text/css">
	table.db-table      { border-right:1px solid #ccc; border-bottom:1px solid #ccc; }
	table.db-table th   { background:#eee; padding:5px; border-left:1px solid #ccc; border-top:1px solid #ccc; }
	table.db-table td   { padding:5px; border-left:1px solid #ccc; border-top:1px solid #ccc; }
	.save-button		{ margin-top: 10px; }
	.active 			{ font-weight: bold; text-decoration: underline; }
</style>


<div>

<?php

elgg_load_library('c_ext_lib');


if (($vars['entity']->db_add_ext === '' || !isset($vars['entity']->db_add_ext)) && ($vars['entity']->db_add_dept === '' || !isset($vars['entity']->db_add_dept)))
{
	// nothing here
} else
	addExtension($vars['entity']->db_add_ext, $vars['entity']->db_add_dept);

function getActiveClass($type, $value){
	return ($_GET[$type] == $value) ? " class='active'" : "";
}

$vars['entity']->db_add_ext = '';
$vars['entity']->db_add_dept = '';


elgg_load_library('c_ext_lib');

$add_to_db = "action/c_email_extensions/add";
$add_btn = elgg_view('output/confirmlink', array(
	'name' => 'c_add_to_db',
	'text' => elgg_echo('c_ext:add'),
	'href' => $add_to_db,
	'class' => 'elgg-button'));

$add_ext_field = elgg_view('input/text', array(
	'name' => 'params[db_add_ext]',
	'value' => $vars['entity']->db_add_ext));

$add_dept_field = elgg_view('input/text', array(
	'name' => 'params[db_add_dept]',
	'value' => $vars['entity']->db_add_dept));

echo '<br/>';
echo "<table name='add_extensions' width='100%' cellpadding='0' cellspacing='0' class='db-table'>";
echo '<tr> <th>'.elgg_echo('c_ext:add_new_ext').'</th> </tr>';
echo '<tr><td>';
echo elgg_echo('c_ext:ext').':'.$add_ext_field.'<br/>';
echo elgg_echo('c_ext:dept').':'.$add_dept_field.'<br/>';
//echo $add_btn.'<br/>';
echo '</td></tr>';
echo "</table>";

$plugin = $vars['entity'];
$plugin_id = $plugin->getID();
$user_guid = elgg_extract('user_guid', $vars, elgg_get_logged_in_user_guid());

echo '<div class="save-button">';
echo elgg_view('input/hidden', array('name' => 'plugin_id', 'value' => $plugin_id));
echo elgg_view('input/hidden', array('name' => 'user_guid', 'value' => $user_guid));
echo elgg_view('input/submit', array('value' => elgg_echo('save')));
echo '</div>';

echo '<br/>';

$delete_from_db = "action/c_email_extensions/delete";
$delete_btn = elgg_view('output/confirmlink', array(
	'name' => 'c_delete_from_db',
	'text' => elgg_echo('c_ext:delete'),
	'href' => $delete_from_db));


$sort = $_GET['sort'];
$sort_param = ($sort ? "&sort=" . $sort : "");
$filter = $_GET['filter'];
$filter_param = ($filter ? "&filter=" . $filter : "");
$domains = getExtension($sort,$filter);


if (count($domains) > 0) {

echo "SORT (departments): <a".getActiveClass('sort', 'asc')." href='?sort=asc".$filter_param."'>A-Z</a> / <a".getActiveClass('sort', 'desc')." href='?sort=desc".$filter_param."'>Z-A</a> <br/>";
echo "FILTER: <a".getActiveClass('filter', 'university')." href='?filter=university".$sort_param."'>University</a> / <a".getActiveClass('filter', 'department')." href='?filter=department".$sort_param."'>Government Departments</a> / <a".getActiveClass('filter', 'all')." href='?filter=all".$sort_param."'>All</a> <br/>";

	echo "<table name='display_extensions' width='100%' cellpadding='0' cellspacing='0' class='db-table'>";
	echo '<tr> <th></th> <th width="16%">'.elgg_echo('c_ext:id').'</th> <th>'.elgg_echo('c_ext:ext').'</th> <th>'.elgg_echo('c_ext:dept').'</th></tr>';
	foreach ($domains as $domain) {
		$delete_from_db = "action/c_email_extensions/delete?id={$domain->id}";
		$delete_btn = elgg_view('output/confirmlink', array(
			'name' => 'c_delete_from_db',
			'text' => elgg_echo('c_ext:delete'),
			'href' => $delete_from_db));

		echo "<tr>"; 
		echo "<td> {$delete_btn} </td>";
		echo "<td> {$domain->id} </td>";
		echo "<td> {$domain->ext} </td>";
		echo "<td> {$domain->dept} </td>";
		echo "</tr>";
	}
	echo "</table>";
}

?>

</div>