<?php

$value = (int) elgg_extract('value', $vars, 0);
$total = (int) elgg_extract('total', $vars, 100);

$progress = ($value / $total) * 100;
if ($progress > 100) {
	$progress = 100;
}

$class = 'gm-progressbar';
if ($progress == 100) {
	$class .= ' gm-progressbar-complete';
} else if ($progress > 50) {
	$class .= ' gm-progressbar-pending';
}

$style = "width:{$progress}%;"
?>
<div class="<?php echo $class ?>">
	<span style="<?php echo $style ?>"></span>
</div>
