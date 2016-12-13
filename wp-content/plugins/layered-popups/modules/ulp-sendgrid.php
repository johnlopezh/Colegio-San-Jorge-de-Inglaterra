<?php
/* SendGrid integration for Layered Popups */
class ulp_sendgrid_class {
	var $default_popup_options = array(
		'sendgrid_enable' => 'off',
		'sendgrid_login' => '',
		'sendgrid_api_key' => '',
		'sendgrid_list_id' => '',
		'sendgrid_fields' => ''
	);
	function __construct() {
		if (is_admin()) {
			add_action('admin_init', array(&$this, 'admin_request_handler'));
			add_action('ulp_popup_options_integration_show', array(&$this, 'popup_options_show'));
			add_filter('ulp_popup_options_check', array(&$this, 'popup_options_check'), 10, 1);
			add_filter('ulp_popup_options_populate', array(&$this, 'popup_options_populate'), 10, 1);
			add_filter('ulp_popup_options_tabs', array(&$this, 'popup_options_tabs'), 10, 1);
		}
		add_action('ulp_subscribe', array(&$this, 'subscribe'), 10, 2);
	}
	function popup_options_tabs($_tabs) {
		if (!array_key_exists("integration", $_tabs)) $_tabs["integration"] = __('Integration', 'ulp');
		return $_tabs;
	}
	function popup_options_show($_popup_options) {
		$popup_options = array_merge($this->default_popup_options, $_popup_options);
		$fields = unserialize($popup_options['sendgrid_fields']);
		echo '
				<h3>'.__('SendGrid Parameters', 'ulp').'</h3>
				<table class="ulp_useroptions">
					<tr>
						<th>'.__('Enable SendGrid', 'ulp').':</th>
						<td>
							<input type="checkbox" id="ulp_sendgrid_enable" name="ulp_sendgrid_enable" '.($popup_options['sendgrid_enable'] == "on" ? 'checked="checked"' : '').'"> '.__('Submit contact details to SendGrid', 'ulp').'
							<br /><em>'.__('Please tick checkbox if you want to submit contact details to SendGrid.', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('API User', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_sendgrid_login" name="ulp_sendgrid_login" value="'.esc_html($popup_options['sendgrid_login']).'" class="widefat" onchange="ulp_sendgrid_handler();">
							<br /><em>'.__('This is the same credential used for your SMTP settings, and for logging into SendGrid website.', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('API Key', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_sendgrid_api_key" name="ulp_sendgrid_api_key" value="'.esc_html($popup_options['sendgrid_api_key']).'" class="widefat" onchange="ulp_sendgrid_handler();">
							<br /><em>'.__('This is the same password to authenticate over SMTP, and for logging into SendGrid website.', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('Recipient List', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_sendgrid_list_id" name="ulp_sendgrid_list_id" value="'.esc_html($popup_options['sendgrid_list_id']).'" class="widefat">
							<br /><em>'.__('Enter Recipient List. You can get it from', 'ulp').' <a href="'.admin_url('admin.php').'?action=ulp-sendgrid-lists&login='.base64_encode($popup_options['sendgrid_login']).'&key='.base64_encode($popup_options['sendgrid_api_key']).'" class="thickbox" id="ulp_sendgrid_lists" title="'.__('Available Lists', 'ulp').'">'.__('this table', 'ulp').'</a>.</em>
							<script>
								function ulp_sendgrid_handler() {
									jQuery("#ulp_sendgrid_lists").attr("href", "'.admin_url('admin.php').'?action=ulp-sendgrid-lists&login="+ulp_encode64(jQuery("#ulp_sendgrid_login").val())+"&key="+ulp_encode64(jQuery("#ulp_sendgrid_api_key").val()));
								}
							</script>
						</td>
					</tr>
					<tr>
						<th>'.__('Fields (Columns)', 'ulp').':</th>
						<td>
							'.__('Please adjust the fields/columns below. You can use the same shortcodes (<code>{subscription-email}</code>, <code>{subscription-name}</code>, etc.) to associate SendGrid fields with the popup fields.', 'ulp').'
							<table style="width: 100%;">
								<tr>
									<td style="width: 200px;"><strong>'.__('Name', 'ulp').'</strong></td>
									<td><strong>'.__('Value', 'ulp').'</strong></td>
								</tr>';
		$i = 0;
		if (is_array($fields)) {
			foreach ($fields as $key => $value) {
				echo '									
								<tr>
									<td>
										<input type="text" name="ulp_sendgrid_fields_name[]" value="'.esc_html($key).'" class="widefat">
										<br /><em>'.($i > 0 ? '<a href="#" onclick="return ulp_sendgrid_remove_field(this);">'.__('Remove Field', 'ulp').'</a>' : '').'</em>
									</td>
									<td>
										<input type="text" name="ulp_sendgrid_fields_value[]" value="'.esc_html($value).'" class="widefat">
									</td>
								</tr>';
				$i++;
			}
		}
		if ($i == 0) {
			echo '									
								<tr>
									<td>
										<input type="text" name="ulp_sendgrid_fields_name[]" value="" class="widefat">
									</td>
									<td>
										<input type="text" name="ulp_sendgrid_fields_value[]" value="" class="widefat">
									</td>
								</tr>';
		}
		echo '
								<tr style="display: none;" id="sendgrid-field-template">
									<td>
										<input type="text" name="ulp_sendgrid_fields_name[]" value="" class="widefat">
										<br /><em><a href="#" onclick="return ulp_sendgrid_remove_field(this);">'.__('Remove Field', 'ulp').'</a></em>
									</td>
									<td>
										<input type="text" name="ulp_sendgrid_fields_value[]" value="" class="widefat">
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<a href="#" class="button-secondary" onclick="return ulp_sendgrid_add_field(this);">'.__('Add Field', 'ulp').'</a>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<script>
					function ulp_sendgrid_add_field(object) {
						jQuery("#sendgrid-field-template").before("<tr>"+jQuery("#sendgrid-field-template").html()+"</tr>");
						return false;
					}
					function ulp_sendgrid_remove_field(object) {
						var row = jQuery(object).parentsUntil("tr").parent();
						jQuery(row).fadeOut(300, function() {
							jQuery(row).remove();
						});
						return false;
					}
				</script>';
	}
	function popup_options_check($_errors) {
		global $ulp;
		$errors = array();
		$popup_options = array();
		foreach ($this->default_popup_options as $key => $value) {
			if (isset($ulp->postdata['ulp_'.$key])) {
				$popup_options[$key] = stripslashes(trim($ulp->postdata['ulp_'.$key]));
			}
		}
		if (isset($ulp->postdata["ulp_sendgrid_enable"])) $popup_options['sendgrid_enable'] = "on";
		else $popup_options['sendgrid_enable'] = "off";
		if ($popup_options['sendgrid_enable'] == 'on') {
			if (empty($popup_options['sendgrid_login'])) $errors[] = __('Invalid SendGrid API User', 'ulp');
			if (empty($popup_options['sendgrid_api_key'])) $errors[] = __('Invalid SendGrid API Key', 'ulp');
			if (empty($popup_options['sendgrid_list_id'])) $errors[] = __('Invalid SendGrid List ID', 'ulp');
		}
		return array_merge($_errors, $errors);
	}
	function popup_options_populate($_popup_options) {
		global $ulp;
		$popup_options = array();
		foreach ($this->default_popup_options as $key => $value) {
			if (isset($ulp->postdata['ulp_'.$key])) {
				$popup_options[$key] = stripslashes(trim($ulp->postdata['ulp_'.$key]));
			}
		}
		if (isset($ulp->postdata["ulp_sendgrid_enable"])) $popup_options['sendgrid_enable'] = "on";
		else $popup_options['sendgrid_enable'] = "off";
		if (is_array($ulp->postdata["ulp_sendgrid_fields_name"]) && is_array($ulp->postdata["ulp_sendgrid_fields_value"])) {
			$fields = array();
			for($i=0; $i<sizeof($ulp->postdata["ulp_sendgrid_fields_name"]); $i++) {
				$key = stripslashes(trim($ulp->postdata['ulp_sendgrid_fields_name'][$i]));
				$value = stripslashes(trim($ulp->postdata['ulp_sendgrid_fields_value'][$i]));
				if (!empty($key)) $fields[$key] = $value;
			}
			if (!empty($fields)) $popup_options['sendgrid_fields'] = serialize($fields);
			else $popup_options['sendgrid_fields'] = '';
		} else $popup_options['sendgrid_fields'] = '';
		return array_merge($_popup_options, $popup_options);
	}
	function admin_request_handler() {
		global $wpdb;
		if (!empty($_GET['action'])) {
			switch($_GET['action']) {
				case 'ulp-sendgrid-lists':
					if (isset($_GET["login"]) && isset($_GET["key"])) {
						$login = base64_decode($_GET["login"]);
						$key = base64_decode($_GET["key"]);
						
						$request = http_build_query(array(
							'api_user' => $login,
							'api_key' => $key
						));
						$curl = curl_init('https://api.sendgrid.com/api/newsletter/lists/get.json');
						curl_setopt($curl, CURLOPT_POST, 1);
						curl_setopt($curl, CURLOPT_POSTFIELDS, $request);

						curl_setopt($curl, CURLOPT_TIMEOUT, 20);
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
						curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
						curl_setopt($curl, CURLOPT_HEADER, 0);
						$response = curl_exec($curl);
						curl_close($curl);
						$lists = json_decode($response, true);
						if (!empty($lists) && is_array($lists) && !array_key_exists('error', $lists)) {
							echo '
<html>
<head>
	<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
	<title>'.__('SendGrid Lists', 'ulp').'</title>
</head>
<body>
	<table style="width: 100%;">
		<!--<tr>
			<td style="width: 170px; font-weight: bold;">'.__('List ID', 'ulp').'</td>
			<td style="font-weight: bold;">'.__('List Name', 'ulp').'</td>
		</tr>-->';
							foreach ($lists as $key => $value) {
								echo '
		<tr>
			<!--<td>'.esc_html($value['id']).'</td>-->
			<td>'.esc_html($value['list']).'</td>
		</tr>';
							}
							echo '
	</table>						
</body>
</html>';
						} else echo '<div style="text-align: center; margin: 20px 0px;">'.__('No data found!', 'ulp').'</div>';
					} else echo '<div style="text-align: center; margin: 20px 0px;">'.__('No data found!', 'ulp').'</div>';
					die();
					break;
				default:
					break;
			}
		}
	}
	function subscribe($_popup_options, $_subscriber) {
		if (empty($_subscriber['{subscription-email}'])) return;
		$popup_options = array_merge($this->default_popup_options, $_popup_options);
		if ($popup_options['sendgrid_enable'] == 'on') {
			$data = array(
				'email' => $_subscriber['{subscription-email}'],
				'name' => $_subscriber['{subscription-name}']
			);
			$fields = unserialize($popup_options['sendgrid_fields']);
			if (is_array($fields)) {
				foreach ($fields as $key => $value) {
					$data[$key] = strtr($value, $_subscriber);
				}
			}
			$request = http_build_query(array(
				'data' => json_encode($data),
				'api_user' => $popup_options['sendgrid_login'],
				'api_key' => $popup_options['sendgrid_api_key'],
				'list' => $popup_options['sendgrid_list_id']
			));

			$curl = curl_init('https://api.sendgrid.com/api/newsletter/lists/email/add.json');
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request);

			curl_setopt($curl, CURLOPT_TIMEOUT, 20);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
			curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
			curl_setopt($curl, CURLOPT_HEADER, 0);
								
			$response = curl_exec($curl);
			curl_close($curl);
		}
	}
}
$ulp_sendgrid = new ulp_sendgrid_class();
?>