<?php
/**
 * SAML 2.0 IdP configuration for SimpleSAMLphp.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-hosted
 */

$metadata['ssp-hub.local'] = array(
	/*
	 * The hostname of the server (VHOST) that will use this SAML entity.
	 *
	 * Can be '__DEFAULT__', to use this entry by default.
	 */
	'host' => 'ssp-hub.local',

	// X.509 key and certificate. Relative to the cert directory.
	'privatekey' => 'ssp-hub.pem',
	'certificate' => 'ssp-hub.crt',

	/*
	 * Authentication source to use. Must be one that is configured in
	 * 'config/authsources.php'.
	 */
	'auth' => 'auth-choices',
    'authproc' => [
        95 => [
                  'class' =>'ssphub:TrackIdps',
              ]
    ],
);
