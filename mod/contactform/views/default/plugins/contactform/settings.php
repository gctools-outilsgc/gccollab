<?php 
$email = $vars['entity']->email;
$loginreq = $vars['entity']->loginreq;
$disable_feedback = $vars['entity']->disable_feedback;

if (!$loginreq) { $loginreq = 'yes'; }
if (!$disable_feedback) { $loginreq = 'no'; }			
?>

<p>
<?php
echo elgg_echo('contactform:enteremail');
echo elgg_view('input/text', array(
    'name'  => 'params[email]',
    'value' => $email,
));
echo "<div>";
	echo elgg_echo('contactform:loginreqmsg') . ' ';
	echo elgg_view('input/dropdown', array(
		'name' => 'params[loginreq]',
		'options_values' => array(
			'no' => elgg_echo('contactform:no'),
			'yes' => elgg_echo('contactform:yes'),
		),
		'value' => $loginreq,
	));
echo "</div>";


// in case the feedback form does not work out
echo '<br/>';
echo "Disable Feedback/contact form and display Jeff's email address";
echo elgg_view('input/dropdown', array(
		'name' => 'params[disable_feedback]',
		'options_values' => array(
			'no' => elgg_echo('contactform:no'),
			'yes' => elgg_echo('contactform:yes'),
		),
		'value' => $loginreq,
	));


