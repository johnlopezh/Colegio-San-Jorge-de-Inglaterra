<?php
/* Campaign Monitor integration for Layered Popups */
class ulp_campaignmonitor_class {
	var $default_popup_options = array(
		'campaignmonitor_enable' => "off",
		'campaignmonitor_api_key' => '',
		'campaignmonitor_list_id' => ''
	);
	function __construct() {
		if (is_admin()) {
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
				<h3>'.__('Campaign Monitor Parameters', 'ulp').'</h3>
				<table class="ulp_useroptions">
					<tr>
						<th>'.__('Enable Campaign Monitor', 'ulp').':</th>
						<td>
							<input type="checkbox" id="ulp_campaignmonitor_enable" name="ulp_campaignmonitor_enable" '.($popup_options['campaignmonitor_enable'] == "on" ? 'checked="checked"' : '').'"> '.__('Submit contact details to Campaign Monitor', 'ulp').'
							<br /><em>'.__('Please tick checkbox if you want to submit contact details to Campaign Monitor.', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('API Key', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_campaignmonitor_api_key" name="ulp_campaignmonitor_api_key" value="'.esc_html($popup_options['campaignmonitor_api_key']).'" class="widefat">
							<br /><em>'.__('Enter your Campaign Monitor API Key. You can get your API Key from the Account Settings page when logged into your Campaign Monitor account.', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('List ID', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_campaignmonitor_list_id" name="ulp_campaignmonitor_list_id" value="'.esc_html($popup_options['campaignmonitor_list_id']).'" class="widefat">
							<br /><em>'.__('Enter your List ID. You can get List ID from the list editor page when logged into your Campaign Monitor account.', 'ulp').'</em>
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
		if (isset($ulp->postdata["ulp_campaignmonitor_enable"])) $popup_options['campaignmonitor_enable'] = "on";
		else $popup_options['campaignmonitor_enable'] = "off";
		if ($popup_options['campaignmonitor_enable'] == 'on') {
			if (empty($popup_options['campaignmonitor_api_key'])) $errors[] = __('Invalid Campaign Monitor API Key.', 'ulp');
			if (empty($popup_options['campaignmonitor_list_id'])) $errors[] = __('Invalid Campaign Monitor List ID.', 'ulp');
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
		if (isset($ulp->postdata["ulp_campaignmonitor_enable"])) $popup_options['campaignmonitor_enable'] = "on";
		else $popup_options['campaignmonitor_enable'] = "off";
		return array_merge($_popup_options, $popup_options);
	}
	function subscribe($_popup_options, $_subscriber) {
		if (empty($_subscriber['{subscription-email}'])) return;
		$popup_options = array_merge($this->default_popup_options, $_popup_options);
		if ($popup_options['campaignmonitor_enable'] == 'on') {
			$options['EmailAddress'] = $_subscriber['{subscription-email}'];
			$options['Name'] = $_subscriber['{subscription-name}'];
			$options['Resubscribe'] = 'true';
			$options['RestartSubscriptionBasedAutoresponders'] = 'true';
			$post = json_encode($options);

			$curl = curl_init('http://api.createsend.com/api/v3/subscribers/'.urlencode($popup_options['campaignmonitor_list_id']).'.json');
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
				
			$header = array(
				'Content-Type: application/json',
				'Content-Length: '.strlen($post),
				'Authorization: Basic '.base64_encode($popup_options['campaignmonitor_api_key'])
				);

			//curl_setopt($curl, CURLOPT_PORT, 443);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			curl_setopt($curl, CURLOPT_TIMEOUT, 10);
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
			//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1); // verify certificate
			//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // check existence of CN and verify that it matches hostname
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
			curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
					
			$response = curl_exec($curl);
			curl_close($curl);
		}
	}
}
$ulp_campaignmonitor = new ulp_campaignmonitor_class();
?>