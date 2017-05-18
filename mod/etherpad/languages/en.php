<?php
/**
 * Etherpads English language file
 * 
 * package ElggPad
 */

$english = array(

	/**
	 * Menu items and titles
	 */
	 
	'etherpad' => "Pads",
	'etherpad:owner' => "%s's pads",
	'etherpad:friends' => "Friends' pads",
	'etherpad:all' => "All site pads",
	'etherpad:add' => "Add pad",
	'etherpad:timeslider' => 'History',
	'etherpad:fullscreen' => 'Fullscreen',
	'etherpad:none' => 'No pads created yet',
	
	'etherpad:group' => 'Group pads',
	'groups:enablepads' => 'Enable group pads',
	
	/**
	 * River
	 */
	'river:create:object:etherpad' => '%s created a new collaborative pad %s',
	'river:create:object:subpad' => '%s created a new collaborative pad %s',
	'river:update:object:etherpad' => '%s updated the collaborative pad %s',
	'river:update:object:subpad' => '%s updated the collaborative pad %s',
	'river:comment:object:etherpad' => '%s commented on the collaborative pad %s',
	'river:comment:object:subpad' => '%s commented on the collaborative pad %s',
	
	'item:object:etherpad' => 'Pads',
	'item:object:subpad' => 'Subpads',

	/**
	 * Status messages
	 */

	'etherpad:saved' => "Your pad was successfully saved.",
	'etherpad:delete:success' => "Your pad was successfully deleted.",
	'etherpad:delete:failure' => "Your pad could not be deleted. Please try again.",
	
	/**
	 * Edit page
	 */
	 
	 'etherpad:title' => "Title",
	 'etherpad:tags' => "Tags",
	 'etherpad:access_id' => "Read access",
	 'etherpad:write_access_id' => "Write access",

	/**
	 * Admin settings
	 */

	'etherpad:etherpadhost' => "Etherpad lite host address:",
	'etherpad:etherpadkey' => "Etherpad lite api key:",
	'etherpad:showchat' => "Show chat?",
	'etherpad:linenumbers' => "Show line numbers?",
	'etherpad:showcontrols' => "Show controls?",
	'etherpad:monospace' => "Use monospace font?",
	'etherpad:showcomments' => "Show comments?",
	'etherpad:newpadtext' => "New pad text:",
	'etherpad:pad:message' => 'New pad created successfully.',
	'etherpad:integrateinpages' => "Integrate pads and pages? (Requires Pages plugin to be enabled)",
	
	/**
	 * Widget
	 */
	'etherpad:profile:numbertodisplay' => "Number of pads to display",
    'etherpad:profile:widgetdesc' => "Display your latest pads",
    
    /**
	 * Sidebar items
	 */
	'etherpad:newchild' => "Create a sub-pad",
);

add_translation('en', $english);
