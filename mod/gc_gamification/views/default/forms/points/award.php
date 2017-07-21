<?php
$entity = elgg_extract('entity', $vars);

if (!elgg_instanceof($entity, 'user')) {
	return;
}

if (!$entity->canAnnotate(0, 'gm_score_award')) {
	return;
}

echo '<div class="elgg-head mbl">';
echo elgg_view_title($entity->name);
echo elgg_view('framework/mechanics/user_score', $vars);
echo '</div>';

echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('mechanics:admin:award:amount'),
	'name' => 'amount',
	'required' => true,
	'value' => elgg_extract('amount', $vars, 0),
]);

echo elgg_view_field([
	'#type' => 'plaintext',
	'#label' => elgg_echo('mechanics:admin:award:note'),
	'name' => 'note',
	'value' => elgg_extract('note', $vars, ''),
	'rows' => 2,
]);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'guid',
	'value' => $entity->guid
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('mechanics:admin:award'),
]);

elgg_set_form_footer($footer);

?>
<script>
	require(['framework/mechanics/award']);
</script>