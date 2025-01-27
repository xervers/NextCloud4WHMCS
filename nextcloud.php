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
    $proto = $params['serversecure'] ? 'https' : 'http';
    
    # Query for user
    $urluser = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users';
    $urluser4log = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users';
    
    # Query for groups
    $urlgroup = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/groups';
    $urlgroup4log = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/groups';
    
    # Check if group exists on NextCloud and create it if it doesn't
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urlgroup . '?search=' . $params['customfields']['nextcloud_group']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/xml'));
    if ($params['serverusername'] === 'token') {
        $bearerToken = $params['serverpassword'];
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'OCS-APIRequest: true',
            'Accept: application/xml',
            'Authorization: Bearer ' . $bearerToken
        ));
    } else {
        curl_setopt($ch, CURLOPT_USERPWD, $params['serverusername'] . ':' . $params['serverpassword']);
    }
    $result = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    # Update WHMCS product and logs
    $resultrequest = simplexml_load_string($result);
    logModuleCall('nextcloud', __FUNCTION__, "GET " . $urlgroup4log . '?search=' . $params['customfields']['group'], "", $result, $resultrequest, $curl_error);
    
    if (empty($resultrequest->data->groups)) {
        logModuleCall('nextcloud', __FUNCTION__, "POST " . $urlgroup4log, "", 'Group doesn\'t exist. Creating it...');
        
        # Create the group if it doesn't exist
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlgroup);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('groupid' => $params['customfields']['nextcloud_group'])));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/xml'));
        if ($params['serverusername'] === 'token') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'OCS-APIRequest: true',
                'Accept: application/xml',
                'Authorization: Bearer ' . $bearerToken
            ));
        } else {
            curl_setopt($ch, CURLOPT_USERPWD, $params['serverusername'] . ':' . $params['serverpassword']);
        }
        $result = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);

        # Update WHMCS product and logs
        $resultrequest = simplexml_load_string($result);
        logModuleCall('nextcloud', __FUNCTION__, "POST " . $urlgroup4log, "", $result, $resultrequest, $curl_error);
    }
    
    # Add user to NextCloud
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urluser);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
        'userid' => $params['username'],
        'password' => $params['password'],
        'displayName' => $params['clientsdetails']['clientname'],
        'email' => $params['clientsdetails']['email'],
        'phone' => $params['clientsdetails']['phonenumber'],
        'address' => $params['clientsdetails']['address1'] . ', ' . $params['clientsdetails']['address2'] . ', ' . $params['clientsdetails']['postcode'] . ', ' . $params['clientsdetails']['city'] . ', ' . $params['clientsdetails']['country'],
        'organisation' => $params['clientsdetails']['companyname'],
        'groups' => $params['customfields']['nextcloud_group'],
        'quota' => $params['configoptions']['quota'] * 1024 * 1024 * 1024,
        'language' => $params['customfields']['language']
    )));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/xml'));
    if ($params['serverusername'] === 'token') {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'OCS-APIRequest: true',
            'Accept: application/xml',
            'Authorization: Bearer ' . $bearerToken
        ));
    } else {
        curl_setopt($ch, CURLOPT_USERPWD, $params['serverusername'] . ':' . $params['serverpassword']);
    }
    $result = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    # Update WHMCS product and logs
    $resultrequest = simplexml_load_string($result);
    logModuleCall('nextcloud', __FUNCTION__, "POST " . $urluser4log, "", $result, $resultrequest, $curl_error);
    
    if ($resultrequest->meta->statuscode == 200) {
        return 'success';
    } else {
        return $resultrequest->meta->message;
    }
}

function nextcloud_SuspendAccount(array $params) {
    # Is NextCloud on HTTP or HTTPS?
    $proto = $params['serversecure'] ? 'https' : 'http';
    
    # Query for user
    $urluser = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'] . '/disable';
    $urluser4log = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'] . '/disable';
    
    # Disable user on NextCloud
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urluser);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/xml'));
    
    if ($params['serverusername'] === 'token') {
        # Use Bearer token for authentication
        $bearerToken = $params['serverpassword'];
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'OCS-APIRequest: true',
            'Accept: application/xml',
            'Authorization: Bearer ' . $bearerToken
        ));
    } else {
        # Use Basic Authentication
        curl_setopt($ch, CURLOPT_USERPWD, $params['serverusername'] . ':' . $params['serverpassword']);
    }

    $result = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    # Update WHMCS product and logs
    $resultrequest = simplexml_load_string($result);
    logModuleCall('nextcloud', __FUNCTION__, "PUT " . $urluser4log, '', $result, $resultrequest, $curl_error);
    
    if ($curl_error) {
        return array('success' => false, 'error' => 'cURL Error: ' . $curl_error);
    }
    
    if ($resultrequest->meta->statuscode == 200) {
        return 'success';
    } else {
        return (string)$resultrequest->meta->message;
    }
}

function nextcloud_UnsuspendAccount(array $params) {
    # Is NextCloud on HTTP or HTTPS?
    $proto = $params['serversecure'] ? 'https' : 'http';
    
    # Query for user
    $urluser = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'] . '/enable';
    $urluser4log = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'] . '/enable';
    
    # Enable user on NextCloud
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urluser);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/xml'));
    
    if ($params['serverusername'] === 'token') {
        # Use Bearer token for authentication
        $bearerToken = $params['serverpassword'];
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'OCS-APIRequest: true',
            'Accept: application/xml',
            'Authorization: Bearer ' . $bearerToken
        ));
    } else {
        # Use Basic Authentication
        curl_setopt($ch, CURLOPT_USERPWD, $params['serverusername'] . ':' . $params['serverpassword']);
    }

    $result = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    # Update WHMCS product and logs
    $resultrequest = simplexml_load_string($result);
    logModuleCall('nextcloud', __FUNCTION__, "PUT " . $urluser4log, '', $result, $resultrequest, $curl_error);
    
    if ($curl_error) {
        return array('success' => false, 'error' => 'cURL Error: ' . $curl_error);
    }
    
    if ($resultrequest->meta->statuscode == 200) {
        return 'success';
    } else {
        return (string)$resultrequest->meta->message;
    }
}

function nextcloud_TerminateAccount(array $params) {
    # Is NextCloud on HTTP or HTTPS?
    $proto = $params['serversecure'] ? 'https' : 'http';
    
    # Query for user
    $urluser = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'];
    $urluser4log = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'];
    
    # Delete user from NextCloud
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urluser);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/xml'));

    if ($params['serverusername'] === 'token') {
        # Use Bearer token for authentication
        $bearerToken = $params['serverpassword'];
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'OCS-APIRequest: true',
            'Accept: application/xml',
            'Authorization: Bearer ' . $bearerToken
        ));
    } else {
        # Use Basic Authentication
        curl_setopt($ch, CURLOPT_USERPWD, $params['serverusername'] . ':' . $params['serverpassword']);
    }

    $result = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    # Update WHMCS product and logs
    $resultrequest = simplexml_load_string($result);
    logModuleCall('nextcloud', __FUNCTION__, "DELETE " . $urluser4log, '', $result, $resultrequest, $curl_error);

    if ($curl_error) {
        return array('success' => false, 'error' => 'cURL Error: ' . $curl_error);
    }

    if ($resultrequest->meta->statuscode == 200) {
        return 'success';
    } else {
        return (string)$resultrequest->meta->message;
    }
}

function nextcloud_ChangePassword(array $params) {
    # Is NextCloud on HTTP or HTTPS?
    $proto = $params['serversecure'] ? 'https' : 'http';
    
    # Query for user
    $urluser = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'];
    $urluser4log = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'];
    
    # Change user's password on NextCloud
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urluser);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
        'key' => 'password',
        'value' => $params['password']
    )));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/xml'));
    
    if ($params['serverusername'] === 'token') {
        # Use Bearer token for authentication
        $bearerToken = $params['serverpassword'];
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'OCS-APIRequest: true',
            'Accept: application/xml',
            'Authorization: Bearer ' . $bearerToken
        ));
    } else {
        # Use Basic Authentication
        curl_setopt($ch, CURLOPT_USERPWD, $params['serverusername'] . ':' . $params['serverpassword']);
    }

    $result = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    # Update WHMCS product and logs
    $resultrequest = simplexml_load_string($result);
    logModuleCall('nextcloud', __FUNCTION__, "PUT " . $urluser4log, '', $result, $resultrequest, $curl_error);
    
    if ($curl_error) {
        return array('success' => false, 'error' => 'cURL Error: ' . $curl_error);
    }

    if ($resultrequest->meta->statuscode == 200) {
        return 'success';
    } else {
        return (string)$resultrequest->meta->message;
    }
}

function nextcloud_ChangePackage(array $params) {
    # Is NextCloud on HTTP or HTTPS?
    $proto = $params['serversecure'] ? 'https' : 'http';
    
    # Query for user
    $urluser = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'];
    $urluser4log = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'];
    
    # Query for groups
    $urlgroup = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/groups';
    $urlgroup4log = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/groups';
    
    # Check if group exists on NextCloud and create it if it doesn't
    $customfields = $params['customfields'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urlgroup . '?search=' . $customfields['nextcloud_group']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/xml'));
    
    if ($params['serverusername'] === 'token') {
        # Use Bearer token for authentication
        $bearerToken = $params['serverpassword'];
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'OCS-APIRequest: true',
            'Accept: application/xml',
            'Authorization: Bearer ' . $bearerToken
        ));
    } else {
        # Use Basic Authentication
        curl_setopt($ch, CURLOPT_USERPWD, $params['serverusername'] . ':' . $params['serverpassword']);
    }
    
    $result = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    # Update WHMCS product and logs
    $resultrequest = simplexml_load_string($result);
    logModuleCall('nextcloud', __FUNCTION__, "GET " . $urlgroup4log . '?search=' . $customfields['nextcloud_group'], "", $result, $resultrequest, $curl_error);
    
    if (empty($resultrequest->data->groups)) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlgroup);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('groupid' => $customfields['nextcloud_group'])));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/xml'));
        if ($params['serverusername'] === 'token') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'OCS-APIRequest: true',
                'Accept: application/xml',
                'Authorization: Bearer ' . $bearerToken
            ));
        } else {
            curl_setopt($ch, CURLOPT_USERPWD, $params['serverusername'] . ':' . $params['serverpassword']);
        }
        $result = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);
        logModuleCall('nextcloud', __FUNCTION__, "POST " . $urlgroup4log, "", 'Group doesn\'t exist. Creating it...', $result, $curl_error);
    }
    
    # Change user's quota on NextCloud
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urluser);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('key' => 'quota', 'value' => $params['configoptions']['quota'] * 1024 * 1024 * 1024)));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/xml'));
    
    if ($params['serverusername'] === 'token') {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'OCS-APIRequest: true',
            'Accept: application/xml',
            'Authorization: Bearer ' . $bearerToken
        ));
    } else {
        curl_setopt($ch, CURLOPT_USERPWD, $params['serverusername'] . ':' . $params['serverpassword']);
    }

    $result = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    # Update WHMCS product and logs
    logModuleCall('nextcloud', __FUNCTION__, "PUT " . $urluser4log, "", $result, null, $curl_error);
    
    # Change user's group on NextCloud
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urluser . '/groups');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('groupid' => $customfields['nextcloud_group'])));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/xml'));
    
    if ($params['serverusername'] === 'token') {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'OCS-APIRequest: true',
            'Accept: application/xml',
            'Authorization: Bearer ' . $bearerToken
        ));
    } else {
        curl_setopt($ch, CURLOPT_USERPWD, $params['serverusername'] . ':' . $params['serverpassword']);
    }
    
    $result = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    # Update WHMCS product and logs
    $resultrequest = simplexml_load_string($result);
    logModuleCall('nextcloud', __FUNCTION__, "POST " . $urluser4log . '/groups', "", $result, $resultrequest, $curl_error);
    
    if ($resultrequest->meta->statuscode == 200) {
        return 'success';
    } else {
        return (string)$resultrequest->meta->message;
    }
}

function nextcloud_TestConnection(array $params) {
    # Is NextCloud on HTTP or HTTPS?
    $proto = $params['serversecure'] ? 'https' : 'http';
    
    # URL for a simple API call that requires authentication
    $url = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ($params['serverusername'] === 'token') {
        # Use Bearer token for authentication
        $bearerToken = $params['serverpassword'];
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'OCS-APIRequest: true',
            'Accept: application/xml',
            'Authorization: Bearer ' . $bearerToken
        ));
    } else {
        # Use Basic Authentication
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'OCS-APIRequest: true',
            'Accept: application/xml'
        ));
        curl_setopt($ch, CURLOPT_USERPWD, $params['serverusername'] . ':' . $params['serverpassword']);
    }

    # Execute cURL request
    $result = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    # Update WHMCS product and logs
    logModuleCall('nextcloud', __FUNCTION__, "GET " . $url, '', $result, null, $curl_error);

    if ($curl_error) {
        return array('success' => false, 'error' => 'cURL Error: ' . $curl_error);
    }

    # Parse XML response
    $resultrequest = simplexml_load_string($result);

    if ($resultrequest && $resultrequest->meta->statuscode == 200) {
        return array('success' => true, 'error' => '');
    } else {
        $error_message = isset($resultrequest->meta->message) ? $resultrequest->meta->message : 'Invalid credentials or user not logged in';
        return array('success' => false, 'error' => $error_message);
    }
}

function nextcloud_AdminServicesTabFields(array $params) {
    try {
        # Is NextCloud on HTTP or HTTPS?
        $proto = $params['serversecure'] ? 'https' : 'http';
        
        # Build NextCloud connection URL
        $urluser = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'];
        
        # Determine authentication method
        $authorizationHeader = '';
        if ($params['serverusername'] === 'token') {
            # Use Bearer token for authentication
            $bearerToken = $params['serverpassword'];
            $authorizationHeader = 'Authorization: Bearer ' . $bearerToken;
        } else {
            # Use Basic Authentication
            $authorizationHeader = 'Authorization: Basic ' . base64_encode($params['serverusername'] . ':' . $params['serverpassword']);
        }
        
        # Encrypt the URL for embedding
        $encrypt_method = "AES-256-CBC";
        $secretkey = hash('sha256', 'NextCloudAPI');
        $iv = substr(hash('sha256', 'NextCloudModule'), 0, 16);
        $key = openssl_encrypt($urluser, $encrypt_method, $secretkey, 0, $iv);
        $key = base64_encode($key);
        
        # Call the service's function, using the values provided by WHMCS in `$params`.
        $response = array();
        
        # Return an array based on the function's response.
        return array(
            'Server Information' => '<embed src="' . $proto . '://' . $_SERVER['HTTP_HOST'] . '/modules/servers/nextcloud/stats.php?key=' . $key . '&auth=' . urlencode($authorizationHeader) . '" width="100%" height="400">'
        );
    } catch (Exception $e) {
        # Record the error in WHMCS's module log.
        logModuleCall(
            'nextcloud',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
        
        # In an error condition, simply return no additional fields to display.
        return array();
    }
}

function nextcloud_ClientArea(array $params) {
    # Is NextCloud on HTTP or HTTPS?
    $proto = $params['serversecure'] ? 'https' : 'http';
    
    # Build NextCloud connection URL
    $urluser = $proto . '://' . $params['serverhostname'] . '/ocs/v2.php/cloud/users/' . $params['username'];
    
    # Determine authentication method
    $authorizationHeader = '';
    if ($params['serverusername'] === 'token') {
        # Use Bearer token for authentication
        $bearerToken = $params['serverpassword'];
        $authorizationHeader = 'Authorization: Bearer ' . $bearerToken;
    } else {
        # Use Basic Authentication
        $authorizationHeader = 'Authorization: Basic ' . base64_encode($params['serverusername'] . ':' . $params['serverpassword']);
    }
    
    # Encrypt the URL for embedding
    $encrypt_method = "AES-256-CBC";
    $secretkey = hash('sha256', 'NextCloudAPI');
    $iv = substr(hash('sha256', 'NextCloudModule'), 0, 16);
    $key = openssl_encrypt($urluser, $encrypt_method, $secretkey, 0, $iv);
    $key = base64_encode($key);
        
    $serviceAction = 'get_stats';
    $templateFile = 'templates/overview.tpl';
    
    try {
        # Call the service's function based on the request action, using the values provided by WHMCS in `$params`.
        $response = array();

        $Stats = $proto . '://' . $_SERVER['HTTP_HOST'] . '/modules/servers/nextcloud/stats.php?key=' . $key . '&auth=' . urlencode($authorizationHeader);

        return array(
            'tabOverviewModuleOutputTemplate' => $templateFile,
            'templateVariables' => array(
                'NextCloudStats' => $Stats,
            ),
        );
    } catch (Exception $e) {
        # Record the error in WHMCS's module log.
        logModuleCall('nextcloud', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
    }

    # In an error condition, simply return no additional fields to display.
    return array();
}
