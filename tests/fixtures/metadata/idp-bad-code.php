<?php

return [
    'idp-empty' => [
        'SingleSignOnService'  => 'http://idp-empty/saml2/idp/SSOService.php',
        'IDPCode' => '',
    ],
    'idp-bad' => [
        'SingleSignOnService'  => 'http://idp-bad/saml2/idp/SSOService.php',
        'IDPCode' => 'ba!d!',
    ],
];