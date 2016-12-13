<?php
/* iContact integration for Layered Popups */
class ulp_getresponse_class {
	var $default_popup_options = array(
		'getresponse_enable' => "off",
		'getresponse_api_key' => '',
		'getresponse_campaign_id' => ''
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
		echo '
				<h3>'.__('GetResponse Parameters', 'ulp').'</h3>
				<table class="ulp_useroptions">
					<tr>
						<th>'.__('Enable GetResponse', 'ulp').':</th>
						<td>
							<input type="checkbox" id="ulp_getresponse_enable" name="ulp_getresponse_enable" '.($popup_options['getresponse_enable'] == "on" ? 'checked="checked"' : '').'"> '.__('Submit contact details to GetResponse', 'ulp').'
							<br /><em>'.__('Please tick checkbox if you want to submit contact details to GetResponse.', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('API Key', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_getresponse_api_key" name="ulp_getresponse_api_key" value="'.esc_html($popup_options['getresponse_api_key']).'" class="widefat" onchange="ulp_getresponse_handler();">
							<br /><em>'.__('Enter your GetResponse API Key. You can get your API Key <a href="https://app.getresponse.com/my_api_key.html" target="_blank">here</a>.', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('Campaign ID', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_getresponse_campaign_id" name="ulp_getresponse_campaign_id" value="'.esc_html($popup_options['getresponse_campaign_id']).'" class="widefat">
							<br /><em>'.__('Enter your Campaign ID. You can get Campaign ID from', 'ulp').' <a href="'.admin_url('admin.php').'?action=ulp-getresponse-campaigns&key='.base64_encode($popup_options['getresponse_api_key']).'" class="thickbox" id="ulp_getresponse_campaigns" title="'.__('Available Campaigns', 'ulp').'">'.__('this table', 'ulp').'</a>.</em>
							<script>
								function ulp_getresponse_handler() {
									jQuery("#ulp_getresponse_campaigns").attr("href", "'.admin_url('admin.php').'?action=ulp-getresponse-campaigns&key="+ulp_encode64(jQuery("#ulp_getresponse_api_key").val()));
								}
							</script>
						</td>
					</tr>
				</table>';
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
		if (isset($ulp->postdata["ulp_getresponse_enable"])) $popup_options['getresponse_enable'] = "on";
		else $popup_options['getresponse_enable'] = "off";
		if ($popup_options['getresponse_enable'] == 'on') {
			if (empty($popup_options['getresponse_api_key'])) $errors[] = __('Invalid GetResponse API Key.', 'ulp');
			if (empty($popup_options['getresponse_campaign_id'])) $errors[] = __('Invalid GetResponse Campaign ID.', 'ulp');
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
		if (isset($ulp->postdata["ulp_getresponse_enable"])) $popup_options['getresponse_enable'] = "on";
		else $popup_options['getresponse_enable'] = "off";
		return array_merge($_popup_options, $popup_options);
	}
	function admin_request_handler() {
		global $wpdb;
		if (!empty($_GET['action'])) {
			switch($_GET['action']) {
				case 'ulp-getresponse-campaigns':
					if (isset($_GET["key"]) && !empty($_GET["key"])) {
						$key = base64_decode($_GET["key"]);
						$request = json_encode(
							array(
								'method' => 'get_campaigns',
								'params' => array(
									$key
								),
								'id' => ''
							)
						);

						$curl = curl_init('http://api2.getresponse.com/');
						curl_setopt($curl, CURLOPT_POST, 1);
						curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
						$header = array(
							'Content-Type: application/json',
							'Content-Length: '.strlen($request)
						);
						//curl_setopt($curl, CURLOPT_PORT, 443);
						curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
						curl_setopt($curl, CURLOPT_TIMEOUT, 10);
						//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1); // verify certificate
						//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // check existence of CN and verify that it matches hostname
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
						curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
						curl_setopt($curl, CURLOPT_HEADER, 0);
									
						$response = curl_exec($curl);
						
						if (curl_error($curl)) die('<div style="text-align: center; margin: 20px 0px;">'.__('API call failed.','ulp').'</div>');
						$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
						if ($httpCode != '200') die('<div style="text-align: center; margin: 20px 0px;">'.__('API call failed.','ulp').'</div>');
						curl_close($curl);
						
						$post = json_decode($response, true);
						if(!empty($post['error'])) die('<div style="text-align: center; margin: 20px 0px;">'.__('API Key failed','ulp').': '.$post['error']['message'].'</div>');
						
						if (!empty($post['result'])) {
							echo '
<html>
<head>
	<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
	<title>'.__('GetResponse Campaigns', 'ulp').'</title>
</head>
<body>
	<table style="width: 100%;">
		<tr>
			<td style="width: 170px; font-weight: bold;">'.__('Campaign ID', 'ulp').'</td>
			<td style="font-weight: bold;">'.__('Campaign Name', 'ulp').'</td>
		</tr>';
							foreach ($post['result'] as $key => $value) {
								echo '
		<tr>
			<td>'.esc_html($key).'</td>
			<td>'.esc_html(esc_html($value['name'])).'</td>
		</tr>';
							}
							echo '
	</table>						
</body>
</html>';
						} else echo '<div style="text-align: center; margin: 20px 0px;">'.__('No data found!', 'ulp').'</div>';
					} else echo '<div style="text-align: center; margin: 20px 0px;">'.__('No data found!', 'ulp').'</div>';
					exit;
					break;
				default:
					break;
			}
		}
	}
	function subscribe($_popup_options, $_subscriber) {
		if (empty($_subscriber['{subscription-email}'])) return;
		$popup_options = array_merge($this->default_popup_options, $_popup_options);
		if ($popup_options['getresponse_enable'] == 'on') {
			$request = json_encode(
				array(
					'method' => 'add_contact',
					'params' => array(
						$popup_options['getresponse_api_key'],
						array(
							'campaign' => $popup_options['getresponse_campaign_id'],
							'action' => 'standard',
							'name' => $_subscriber['{subscription-name}'],
							'email' => $_subscriber['{subscription-email}'],
							'cycle_day' => 0,
							'ip' => $_SERVER['REMOTE_ADDR']
						)
					),
					'id' => ''
				)
			);

			$curl = curl_init('http://api2.getresponse.com/');
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
							
			$header = array(
				'Content-Type: application/json',
				'Content-Length: '.strlen($request)
			);

			//curl_setopt($curl, CURLOPT_PORT, 443);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			curl_setopt($curl, CURLOPT_TIMEOUT, 10);
			//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1); // verify certificate
			//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // check existence of CN and verify that it matches hostname
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
			curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
			curl_setopt($curl, CURLOPT_HEADER, 0);
						
			$response = curl_exec($curl);
			curl_close($curl);
		}
	}
}
$ulp_getresponse = new ulp_getresponse_class();
?>