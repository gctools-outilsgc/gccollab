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

echo "<div class='form-group'><label for='amount'>" . elgg_echo('mechanics:admin:award:amount') . "</label>";
echo elgg_view('input/text', array(
    'id' => "amount",
    'name' => "amount",
	'value' => elgg_extract('amount', $vars, 0),
	'required' => true
));
echo "</div>";

echo "<div class='form-group'><label for='note'>" . elgg_echo('mechanics:admin:award:note') . "</label>";
echo elgg_view('input/plaintext', array(
    'id' => "note",
    'name' => "note",
	'value' => elgg_extract('note', $vars, ''),
	'rows' => 2
));
echo "</div>";

echo elgg_view('input/hidden', array(
    'name' => "guid",
	'value' => $entity->guid
));

echo elgg_view('input/submit', array(
    'value' => elgg_echo('mechanics:admin:award')
));

?>
<script>
	require(['framework/mechanics/award']);
</script>