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
	 
	'etherpad' => "Docs",
	'etherpad:docs' => "Docs",
	'etherpad:owner' => "%s's docs",
	'etherpad:friends' => "Friends' docs",
	'etherpad:all' => "All site docs",
	'docs:add' => "Create a new doc",
	'etherpad:add' => "Create a new doc",
	'etherpad:timeslider' => 'History',
	'etherpad:fullscreen' => 'Fullscreen',
	'etherpad:none' => 'No docs created yet',
	
	'etherpad:group' => 'Group docs',
	'groups:enablepads' => 'Enable group docs',
	
	/**
	 * River
	 */
	'river:create:object:etherpad' => '%s created a new collaborative doc %s',
	'river:create:object:subpad' => '%s created a new collaborative doc %s',
	'river:update:object:etherpad' => '%s updated the collaborative doc %s',
	'river:update:object:subpad' => '%s updated the collaborative doc %s',
	'river:comment:object:etherpad' => '%s commented on the collaborative doc %s',
	'river:comment:object:subpad' => '%s commented on the collaborative doc %s',
	
	'item:object:etherpad' => 'Docs',
	'item:object:subpad' => 'Subdocs',

	/**
	 * Status messages
	 */

	'etherpad:saved' => "Your doc was successfully saved.",
	'etherpad:delete:success' => "Your doc was successfully deleted.",
	'etherpad:delete:failure' => "Your doc could not be deleted. Please try again.",
	
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
	'etherpad:showfullscreen' => "Show full screen button?",
	'etherpad:showchat' => "Show chat?",
	'etherpad:linenumbers' => "Show line numbers?",
	'etherpad:showcontrols' => "Show controls?",
	'etherpad:monospace' => "Use monospace font?",
	'etherpad:showcomments' => "Show comments?",
	'etherpad:newdoctext' => "New doc text:",
	'etherpad:doc:message' => 'New doc created successfully.',
	'etherpad:integrateinpages' => "Integrate docs and pages? (Requires Pages plugin to be enabled)",
	
	/**
	 * Widget
	 */
	'etherpad:profile:numbertodisplay' => "Number of docs to display",
    'etherpad:profile:widgetdesc' => "Display your latest docs",
    
    /**
	 * Sidebar items
	 */
	'etherpad:newchild' => "Create a subdoc",
);

add_translation('en', $english);
