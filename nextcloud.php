<?php
# Developpment made by Bruno Carvalho (bruno.carvalho@xrv.pt).
#
# Disclaimer:
# Feel free to use and modify it at your will (even for enterprise use). Just keep my credit and add your's.
#

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}
function nextcloud_MetaData()
{
    return array(
        'DisplayName' => 'NextCloud Provisioning Module',
        'APIVersion' => '1.1', // Use API Version 1.1
        'RequiresServer' => true, // Set true if module requires a server to work
        'DefaultNonSSLPort' => '80', // Default Non-SSL Connection Port
        'DefaultSSLPort' => '443', // Default SSL Connection Port
    );
}

function nextcloud_CreateAccount(array $params)
{
	# Is NextCloud on HTTP or HTTPS?
	if ($params['serversecure']) {
		$proto = 'https';
	} else {
		$proto = 'http';
	}
	
	# Query for user
	$urluser = $proto . '://' . $params['serverusername'] . ':' . $params['serverpassword'] . '@' . $params['serverhostname'] . '/ocs/v2.php/cloud/users';
	$urluser4log = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users';
	
	# Query for groups
	$urlgroup = $proto . '://' . $params['serverusername'] . ':' . $params['serverpassword'] . '@' . $params['serverhostname'] . '/ocs/v2.php/cloud/groups';
	$urlgroup4log = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/groups';
	
	# Check if group exists on NextCloud and create it if it doesn't
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $urlgroup . '?search=' . $params['customfields']['nextcloud_group']);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt ($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/json'));
	$result = curl_exec($ch);
	curl_close($ch);

	# Update WHMCS product and logs
	$resultrequest = json_decode($result,TRUE);
	logModuleCall('nextcloud', __FUNCTION__, "PUT " . $urlgroup4log . '?search=' . $params['customfields']['group'], "", $result);
	
	if (empty($resultrequest['ocs']['data']['groups'])) {
		logModuleCall('nextcloud', __FUNCTION__, "PUT " . $urlgroup4log . '?search=' . $params['customfields']['group'], "", 'Group doesn\'t exist. Creating it...');
	}
	
	# Add user to NextCloud
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $urluser);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLOPT_POST, true);
	curl_setopt ($ch, CURLOPT_POSTFIELDS,
		http_build_query (array(
			'userid' => $params['username'],
			'password' => $params['password'],
			'displayName' => $params['clientsdetails']['clientname'],
			'email' => $params['clientsdetails']['email'],
			'phone' => $params['clientdetails']['phonenumber'],
			'address' => $params['clientdetails']['address1'] . ', ' . $params['clientdetails']['address2'] . ', ' . $params['clientdetails']['postcode'] . ', ' . $params['clientdetails']['city'] . ', ' . $params['clientdetails']['country'],
			'organisation' => $params['clientdetails']['companyname'],
			'groups' => $params['customfields']['nextcloud_group'],
			'quota' => $params['configoptions']['quota'] * 1024 * 1024 * 1024,
			'language' => $params['customfields']['language']
		))
	);
	curl_setopt ($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt ($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/json'));
	$result = curl_exec($ch);
	curl_close($ch);

	# Update WHMCS product and logs
	$resultrequest = json_decode($result,TRUE);
	logModuleCall('nextcloud', __FUNCTION__, "POST " . $urluser4log, "", $result);
	
	if ($resultrequest['ocs']['meta']['statuscode'] == 200) {
		return 'success';
	} else {
		return $resultrequest['ocs']['meta']['message'];
	}
}

function nextcloud_SuspendAccount(array $params)
{
	# Is NextCloud on HTTP or HTTPS?
	if ($params['serversecure']) {
		$proto = 'https';
	} else {
		$proto = 'http';
	}
	
	# Query for user
	$urluser = $proto . '://' . $params['serverusername'] . ':' . $params['serverpassword'] . '@' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'] . '/disable';
	$urluser4log = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'] . '/disable';
	
	# Disable user on NextCloud
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $urluser);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt ($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt ($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/json'));
	$result = curl_exec($ch);
	curl_close($ch);
	
	# Update WHMCS product and logs
	$resultrequest = json_decode($result,TRUE);
	logModuleCall('nextcloud', __FUNCTION__, "PUT " . $urluser4log, "", $result);
	
	if ($resultrequest['ocs']['meta']['statuscode'] == 200) {
		return 'success';
	} else {
		return $resultrequest['ocs']['meta']['message'];
	}
}

function nextcloud_UnsuspendAccount(array $params)
{
	# Is NextCloud on HTTP or HTTPS?
	if ($params['serversecure']) {
		$proto = 'https';
	} else {
		$proto = 'http';
	}
	
	# Query for user
	$urluser = $proto . '://' . $params['serverusername'] . ':' . $params['serverpassword'] . '@' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'] . '/enable';
	$urluser4log = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'] . '/enable';
	
	# Enable user on NextCloud
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $urluser);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt ($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt ($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/json'));
	$result = curl_exec($ch);
	curl_close($ch);
	
	# Update WHMCS product and logs
	$resultrequest = json_decode($result,TRUE);
	logModuleCall('nextcloud', __FUNCTION__, "PUT " . $urluser4log, "", $result);
	
	if ($resultrequest['ocs']['meta']['statuscode'] == 200) {
		return 'success';
	} else {
		return $resultrequest['ocs']['meta']['message'];
	}
}

function nextcloud_TerminateAccount(array $params)
{
	# Is NextCloud on HTTP or HTTPS?
	if ($params['serversecure']) {
		$proto = 'https';
	} else {
		$proto = 'http';
	}
	
	# Query for user
	$urluser = $proto . '://' . $params['serverusername'] . ':' . $params['serverpassword'] . '@' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'];
	$urluser4log = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'];
	
	# Delete user from NextCloud
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $urluser);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
	curl_setopt ($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt ($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/json'));
	$result = curl_exec($ch);
	curl_close($ch);
	
	# Update WHMCS product and logs
	$resultrequest = json_decode($result,TRUE);
	logModuleCall('nextcloud', __FUNCTION__, "DELETE " . $urluser4log, "", $result);
	
	if ($resultrequest['ocs']['meta']['statuscode'] == 200) {
		return 'success';
	} else {
		return $resultrequest['ocs']['meta']['message'];
	}
}

function nextcloud_ChangePassword(array $params)
{
	# Is NextCloud on HTTP or HTTPS?
	if ($params['serversecure']) {
		$proto = 'https';
	} else {
		$proto = 'http';
	}
	
	# Query for user
	$urluser = $proto . '://' . $params['serverusername'] . ':' . $params['serverpassword'] . '@' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'];
	$urluser4log = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'];
	
	# Change user's password on NextCloud
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $urluser);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt ($ch, CURLOPT_POSTFIELDS,
		http_build_query (array(
			'key' => 'password',
			'value' => $params['password']
		))
	);
	curl_setopt ($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt ($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/json'));
	$result = curl_exec($ch);
	curl_close($ch);

	# Update WHMCS product and logs
	$resultrequest = json_decode($result,TRUE);
	logModuleCall('nextcloud', __FUNCTION__, "PUT " . $urluser4log, "", $result);
	
	if ($resultrequest['ocs']['meta']['statuscode'] == 200) {
		return 'success';
	} else {
		return $resultrequest['ocs']['meta']['message'];
	}
}

function nextcloud_ChangePackage(array $params)
{
	# Is NextCloud on HTTP or HTTPS?
	if ($params['serversecure']) {
		$proto = 'https';
	} else {
		$proto = 'http';
	}
	
	# Query for user
	$urluser = $proto . '://' . $params['serverusername'] . ':' . $params['serverpassword'] . '@' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'];
	$urluser4log = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'];
	
	# Query for groups
	$urlgroup = $proto . '://' . $params['serverusername'] . ':' . $params['serverpassword'] . '@' . $params['serverhostname'] . '/ocs/v2.php/cloud/groups';
	$urlgroup4log = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/groups';
	
	# Check if group exists on NextCloud and create it if it doesn't
	$customfields = $params['customfields'];
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $urlgroup . '?search=' . $customfields['nextcloud_group']);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt ($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/json'));
	$result = curl_exec($ch);
	curl_close($ch);

	# Update WHMCS product and logs
	$resultrequest = json_decode($result,TRUE);
	logModuleCall('nextcloud', __FUNCTION__, "GET " . $urlgroup4log . '?search=' . $customfields['nextcloud_group'], "", $result);
	
	if (empty($resultrequest['ocs']['data']['groups'])) {
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $urlgroup);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($ch, CURLOPT_POST, true);
		curl_setopt ($ch, CURLOPT_POSTFIELDS,
			http_build_query (array(
				'groupid' => $customfields['nextcloud_group']
			))
		);
		curl_setopt ($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt ($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/json'));
		$result = curl_exec($ch);
		curl_close($ch);
		logModuleCall('nextcloud', __FUNCTION__, "POST " . $urlgroup4log . '?search=' . $customfields['nextcloud_group'], "", 'Group doesn\'t exist. Creating it...');
	}
	
	# Change user's quota on NextCloud
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $urluser);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt ($ch, CURLOPT_POSTFIELDS,
		http_build_query (array(
			'key' => 'quota',
			'value' => $params['configoptions']['quota'] * 1024 * 1024 * 1024
		))
	);
	curl_setopt ($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt ($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/json'));
	$result = curl_exec($ch);
	curl_close($ch);
	
	# Update WHMCS product and logs
	logModuleCall('nextcloud', __FUNCTION__, "PUT " . $urluser4log, "", $result);
	
	# Change user's group on NextCloud
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $urluser . '/groups');
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLOPT_POST, true);
	curl_setopt ($ch, CURLOPT_POSTFIELDS,
		http_build_query (array(
			'groupid' => $customfields['nextcloud_group']
		))
	);
	curl_setopt ($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt ($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/json'));
	$result = curl_exec($ch);
	curl_close($ch);

	# Update WHMCS product and logs
	$resultrequest = json_decode($result,TRUE);
	logModuleCall('nextcloud', __FUNCTION__, "POST " . $urluser4log . '/groups', "", $result);
	
	if ($resultrequest['ocs']['meta']['statuscode'] == 200) {
		return 'success';
	} else {
		return $resultrequest['ocs']['meta']['message'];
	}
}

function nextcloud_TestConnection(array $params)
{
	# Is NextCloud on HTTP or HTTPS?
	if ($params['serversecure']) {
		$proto = 'https';
	} else {
		$proto = 'http';
	}
	
	# Query for user
	$urluser = $proto . '://' . $params['serverusername'] . ':' . $params['serverpassword'] . '@' . $params['serverhostname'] . '/ocs/v2.php/cloud/users?search=' . $params['serverusername'];
	$urluser4log = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users?search=' . $params['serverusername'];
	
	# Test if the NextCloud admin user can be parsed.
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $urluser);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt ($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/json'));
	$result = curl_exec($ch);
	curl_close($ch);

	# Update WHMCS product and logs
	$resultrequest = json_decode($result,TRUE);
	logModuleCall('nextcloud', __FUNCTION__, "PUT " . $urluser4log, "", $result);
	
	if ($resultrequest['ocs']['meta']['statuscode'] == 200) {
		return array(
			'success' => true,
			'error' => ''
		);
	} else {
		return array(
			'success' => false,
			'error' => $resultrequest['ocs']['meta']['message']
		);
	}
}

function nextcloud_AdminServicesTabFields(array $params)
{
    try {
		# Is NextCloud on HTTP or HTTPS?
		if ($params['serversecure']) {
			$proto = 'https';
		} else {
			$proto = 'http';
		}
		
		# Build NextCloud connection
        $urluser = $proto . '://' . $params['serverusername'] . ':' . $params['serverpassword'] . '@' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'];
		$encrypt_method = "AES-256-CBC";
        $secretkey = hash('sha256', 'NextCloudAPI');
        $iv = substr(hash('sha256', 'NextCloudModule'), 0, 16);
        $key = openssl_encrypt($urluser, $encrypt_method, $secretkey, 0, $iv);
        $key = base64_encode($key);

		// Call the service's function, using the values provided by WHMCS in
        // `$params`.
        $response = array();

        // Return an array based on the function's response.
		
        return array(
            'Server Information' => '<embed src="' . $proto . '://' . $_SERVER['HTTP_HOST'] . '/modules/servers/nextcloud/stats.php?key=' . $key . '" width="100%" height="400">',
        );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'nextcloud',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        // In an error condition, simply return no additional fields to display.
    }

    return array();
}

function nextcloud_ClientArea(array $params)
{
    # Is NextCloud on HTTP or HTTPS?
    if ($params['serversecure']) {
        $proto = 'https';
    } else {
        $proto = 'http';
    }
    
    # Build NextCloud connection
    $urluser = $proto . '://' . $params['serverusername'] . ':' . $params['serverpassword'] . '@' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'];
    $encrypt_method = "AES-256-CBC";
    $secretkey = hash('sha256', 'NextCloudAPI');
    $iv = substr(hash('sha256', 'NextCloudModule'), 0, 16);
    $key = openssl_encrypt($urluser, $encrypt_method, $secretkey, 0, $iv);
    $key = base64_encode($key);
        
    $serviceAction = 'get_stats';
    $templateFile = 'templates/overview.tpl';
    
    try {
        // Call the service's function based on the request action, using the
        // values provided by WHMCS in `$params`.
        $response = array();

        $Stats = $proto . '://' . $_SERVER['HTTP_HOST'] . '/modules/servers/nextcloud/stats.php?key=' . $key;

        return array(
            'tabOverviewModuleOutputTemplate' => $templateFile,
            'templateVariables' => array(
                'NextCloudStats' => $Stats,
            ),
        );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall('nextcloud', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString() );
    }
}
