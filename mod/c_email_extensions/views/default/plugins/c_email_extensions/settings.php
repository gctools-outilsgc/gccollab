<style type="text/css">
	table.db-table      { border-right:1px solid #ccc; border-bottom:1px solid #ccc; }
	table.db-table th   { background:#eee; padding:5px; border-left:1px solid #ccc; border-top:1px solid #ccc; }
	table.db-table td   { padding:5px; border-left:1px solid #ccc; border-top:1px solid #ccc; }
</style>


<div>

<?php

elgg_load_library('c_ext_lib');


if (($vars['entity']->db_add_ext === '' || !isset($vars['entity']->db_add_ext)) && ($vars['entity']->db_add_dept === '' || !isset($vars['entity']->db_add_dept)))
{
	// nothing here
} else
	addExtension($vars['entity']->db_add_ext, $vars['entity']->db_add_dept);


$vars['entity']->db_add_ext = '';
$vars['entity']->db_add_dept = '';


elgg_load_library('c_ext_lib');

$delete_from_db = "action/c_email_extensions/delete";
$delete_btn = elgg_view('output/confirmlink', array(
	'name' => 'c_delete_from_db',
	'text' => elgg_echo('c_ext:delete'),
	'href' => $delete_from_db));


$sort = $_GET['sort'];
$filter = $_GET['filter'];
$domains = getExtension($sort,$filter);


if (count($domains) > 0) {

echo "SORT (departments): <a href='?sort=asc'> A-Z </a> / <a href='?sort=desc'>Z-A</a> <br/>";
echo "FILTER: <a href='?filter=university'> University </a> / <a href='?filter=department'> Government Departments </a> / <a href='?filter=all'> All </a> <br/>";

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

echo '<br/>';

	
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

echo "<table name='add_extensions' width='100%' cellpadding='0' cellspacing='0' class='db-table'>";
echo '<tr> <th>'.elgg_echo('c_ext:add_new_ext').'</th> </tr>';
echo '<tr><td>';
echo elgg_echo('c_ext:ext').':'.$add_ext_field.'<br/>';
echo elgg_echo('c_ext:dept').':'.$add_dept_field.'<br/>';
//echo $add_btn.'<br/>';
echo '</td></tr>';
echo '<br/>';
echo "</table>";

?>

</div>