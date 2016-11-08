<?php
/**
 * Group search form
 *
 * @uses $vars['entity'] ElggGroup
 */


// cyu - show up only for group pages
if (strcmp(get_context(),'groupSubPage') == 0 || strcmp(get_context(),'group_profile') == 0) {

	// Nick - Modified value in hidden input to grab guid of group page
	$params = array(
		'name' => 'q',
		'class' => 'elgg-input-search mbm pull-left group-tab-menu-search-box',
	    'id' => 'qSearch',
	    'placeholder'=>elgg_echo('wet:search_in_group'),
	);
	echo '<span class="group-search-holder pull-right">';
	echo '<label for="qSearch" class="wb-inv">'.elgg_echo('wet:searchHead').'</label>';
	echo elgg_view('input/text', $params);

	echo elgg_view('input/hidden', array(
		'name' => 'container_guid',
		'value' => elgg_get_page_owner_guid(),
	));
	//Nick - created a new type "group_search" for buttons to put the search icon in the submit button
	echo elgg_view('input/button', array('value' => elgg_echo('wet:searchHead'), 'class'=>'pull-left group-search-button', 'type'=>'group_search'));
	echo '</span>';

}