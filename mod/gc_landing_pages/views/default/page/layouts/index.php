<?php
	$area1widgets = $vars['area1'];
	$area2widgets = $vars['area2'];
	$layoutmode   = $vars['layoutmode']; //edit, index
?>
<div class="row">
    <div class="col-md-8" id="wb-cont">
        <?php gc_landing_pages_show_widget_area($area1widgets) ?>
    </div>
    <div class="col-md-4 pull-right">
        <?php gc_landing_pages_show_widget_area($area2widgets) ?>
    </div>
</div>
