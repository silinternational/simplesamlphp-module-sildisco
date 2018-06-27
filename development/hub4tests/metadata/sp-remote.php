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
        'name' => [
            'en' => 'SP1',
        ],
        'AssertionConsumerService' => 'http://sp1/module.php/saml/sp/saml2-acs.php/hub4tests',
        'SingleLogoutService' => 'http://sp1/module.php/saml/sp/saml2-logout.php/hub4tests',
    ],

    'http://ssp-hub-sp2.local' => [
        'AssertionConsumerService' => 'http://sp2/module.php/saml/sp/saml2-acs.php/hub4tests',
        'SingleLogoutService' => 'http://sp2/module.php/saml/sp/saml2-logout.php/hub4tests',
        'IDPList' => ['http://ssp-hub-idp2.local:8086'],
    ],
// This one should be on the SPList entry of idp but not on idp2
    'http://ssp-hub-sp3.local' => [
        'name' => 'SP3',
        'AssertionConsumerService' => 'http://sp3/module.php/saml/sp/saml2-acs.php/hub4tests',
        'SingleLogoutService' => 'http://sp3/module.php/saml/sp/saml2-logout.php/hub4tests',
        'IDPList' => [
            'http://ssp-hub-idp.local:8085',
            'http://ssp-hub-idp2.local:8086',
            'http://ssp-hub-idp3.local:8087'
        ],
    ],

];
