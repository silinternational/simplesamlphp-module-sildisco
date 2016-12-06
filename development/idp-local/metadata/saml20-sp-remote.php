<?php
/**
 * SAML 2.0 remote SP metadata for SimpleSAMLphp.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-sp-remote
 */

/*
 * Example SimpleSAMLphp SAML 2.0 SP
 */
$metadata['ssp-hub.local'] = array(
	'AssertionConsumerService' => 'http://ssp-hub.local/module.php/saml/sp/saml2-acs.php/hub-discovery',
	'SingleLogoutService' => 'http://ssp-hub.local/module.php/saml/sp/saml2-logout.php/hub-discovery',
);
