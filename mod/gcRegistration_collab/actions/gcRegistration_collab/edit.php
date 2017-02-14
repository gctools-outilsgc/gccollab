<?php

$key = get_input('id');
$dept_en = get_input('dept_en');
$dept_fr = get_input('dept_fr');

if( !empty($key) && !empty($dept_en) && !empty($dept_fr) ){
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
