<?php



$email_content = $vars['email_content'];

$current_year = date('Y');

echo <<<___HTML
<html>
<body>
	<!-- beginning of email template -->
	<div width='100%' bgcolor='#fcfcfc'>
		<div>
			<div>

				<!-- email header -->
		        <div align='center' width='100%' style='background-color:#f5f5f5; padding:20px 30px 15px 30px; font-family: sans-serif; font-size: 12px; color: #79579D'>
		        	{$email_notification_header}
		        </div>
				

				<!-- GCcollab banner -->
		     	<div width='100%' style='padding: 0 0 0 10px; color:#ffffff; font-family: sans-serif; font-size: 35px; line-height:38px; font-weight: bold; background-color:#46246A;'>
		        	<span style='padding: 0 0 0 3px; font-size: 20px; color: #ffffff; font-family: sans-serif;'>GCcollab</span>
		        </div>


		        <!-- email divider -->
		        <div style='height:1px; background:#bdbdbd; border-bottom:1px solid #ffffff'></div>

		        <p>English section</p>
				<div> {$email_content} </div>

           
		        </div>
                    <div style='margin-top:15px; padding: 5px; color: #6d6d6d;'>
                   <div>{$email_notification_footer_fr}{$email_notification_footer_fr2}</div>
                </div>


		        <!-- email divider -->
		        <div style='height:1px; background:#bdbdbd; border-bottom:1px solid #ffffff'></div>

		        <p>French section</p>
		        <div> {$email_content} </div>

		        <!-- email footer -->
		        <div align='center' width='100%' style='background-color:#f5f5f5; padding:20px 30px 15px 30px; font-family: sans-serif; font-size: 10px; color: #79579D'>
		        	GCcollab © {$current_year}
		        </div>

			</div>
		</div>
	</div>
</body>
</html>

___HTML;

