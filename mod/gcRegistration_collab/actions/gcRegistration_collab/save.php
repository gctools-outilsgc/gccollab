<?php

if( !empty($_POST['id']) && !empty($_POST['dept_en']) && !empty($_POST['dept_fr']) ){
	$key = $_POST['id'];
	$dept_en = $_POST['dept_en'];
	$dept_fr = $_POST['dept_fr'];

	$deptObj = elgg_get_entities(array(
	   	'type' => 'object',
	   	'subtype' => 'federal_departments',
	));
	$departments = get_entity($deptObj[0]->guid);

	$depts_en = json_decode($departments->federal_departments_en, true);
	$depts_fr = json_decode($departments->federal_departments_fr, true);

	$depts_en[$key] = $dept_en;
	$depts_fr[$key] = $dept_fr;

	$departments->set('federal_departments_en', json_encode($depts_en));
	$departments->set('federal_departments_fr', json_encode($depts_fr));
	$departments->save();

	echo true;
} else {
	echo false;
}
