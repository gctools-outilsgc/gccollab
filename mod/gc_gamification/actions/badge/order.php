<?php

$priorities = get_input('elgg-object');

$i = 0;
foreach ($priorities as $priority => $guid) {
	$badge = get_entity($guid);
	if (elgg_instanceof($badge) && $badge->canEdit()) {
		if (create_metadata($badge->guid, 'priority', $i, 'int', $entity->owner_guid, ACCESS_PUBLIC)) {
			$reordered[$badge->guid] = $badge->priority;
			$i++;
		}
	}
}
if (elgg_is_xhr()) {
	print json_encode($reordered);
}
