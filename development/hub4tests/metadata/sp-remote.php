<?php
/**
 * SAML 2.0 remote SP metadata for SimpleSAMLphp.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-sp-remote
 */

return [
    /*
     * Example SimpleSAMLphp SAML 2.0 SP
     */
    'http://ssp-hub-sp.local' => [
        'AssertionConsumerService' => 'http://ssp-hub-sp.local:8081/module.php/saml/sp/saml2-acs.php/hub4tests',
        'SingleLogoutService' => 'http://ssp-hub-sp.local:8081/module.php/saml/sp/saml2-logout.php/hub4tests',
    ],

    'http://ssp-hub-sp2.local' => [
        'AssertionConsumerService' => 'http://ssp-hub-sp2.local:8082/module.php/saml/sp/saml2-acs.php/hub4tests',
        'SingleLogoutService' => 'http://ssp-hub-sp2.local:8082/module.php/saml/sp/saml2-logout.php/hub4tests',
        'IDPList' => ['http://ssp-hub-idp2.local:8086'],
    ],
// This one should be on the SPList entry of idp2
    'http://ssp-hub-sp3.local' => [
        'AssertionConsumerService' => 'http://ssp-hub-sp3.local:8083/module.php/saml/sp/saml2-acs.php/hub4tests',
        'SingleLogoutService' => 'http://ssp-hub-sp3.local:8083/module.php/saml/sp/saml2-logout.php/hub4tests',
    ],

];
