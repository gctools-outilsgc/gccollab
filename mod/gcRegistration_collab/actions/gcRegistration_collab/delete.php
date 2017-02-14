<?php

$key = get_input('id');

if( !empty($key) ){
	$deptObj = elgg_get_entities(array(
	   	'type' => 'object',
	   	'subtype' => 'federal_departments',
	));
	$departments = get_entity($deptObj[0]->guid);

	$depts_en = json_decode($departments->federal_departments_en, true);
	$depts_fr = json_decode($departments->federal_departments_fr, true);

	unset($depts_en[$key]);
	unset($depts_fr[$key]);

	$departments->set('federal_departments_en', json_encode($depts_en));
	$departments->set('federal_departments_fr', json_encode($depts_fr));
	$departments->save();

	echo true;
} else {
	echo false;
}
