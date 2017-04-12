<?php 

$I = new AcceptanceTester($scenario);
$I->wantTo('Ensure I can login to Sp1 through Idp1 and must login to Sp2 through Idp2.');
$I->setMaxRedirects(999);
$I->amOnPage('http://sp1');
$I->see('SimpleSAMLphp installation page');
//$I->showWebPage();
$I->click('Authentication');
$I->click('Test configured authentication sources');
$I->see('Test authentication sources');

// Go to the hub
$I->click('hub4tests');
$I->seeCurrentUrlMatches("~/module.php/sildisco/disco.php\?entityID=hub4tests~");
$I->see('Select your identity provider');

// Use Idp1 for Authentication
$I->click(["name" => "idp_http://ssp-hub-idp.local:8085"]);
$I->see("Enter your username and password");


// $I->showWebPage();


$I->fillField('password', 'a');

// $I->showWebPage();

// $formAction = 'http://idp1/module.php/core/loginuserpass.php';
$AuthState = $I->grabValueFrom(['name' => 'AuthState']);


// $I->sendPOST(
        // $formAction, 
        // [
            // 'username' => 'admin',
            // 'password' => 'a',
            // 'AuthState' => $AuthState,
        // ], 
        // []
    // );


// $I->amOnPage('http://sp1/module.php/core/authenticate.php?as=hub4tests');
// $I->showWebPage();

$I->click("Login");


$SAMLResponse = $I->grabValueFrom(['name' => 'SAMLResponse']);
$formAction = 'http://hub4tests/module.php/saml/sp/saml2-acs.php/hub-discovery';
$I->sendPOST(
        $formAction, 
        [
            'SAMLResponse' => $SAMLResponse,
        ], 
        []
    );

$SAMLResponse = $I->grabValueFrom(['name' => 'SAMLResponse']);
$formAction = 'http://sp1/module.php/saml/sp/saml2-acs.php/hub-discovery';
$I->sendPOST(
        $formAction, 
        [
            'SAMLResponse' => $SAMLResponse,
        ], 
        []
    );
    
// POST to the hub
// $formAction = $I->grabAttributeFrom('form', 'action');
// $I->assertTextContains('hub-discovery', $formAction);

// $samlResponse = $I->grabValueFrom(['name' => 'SAMLResponse']);
// $I->sendPOST($formAction, ['SAMLResponse' => $samlResponse], []);
// $I->showWebPage();

// POST to Sp1
// $formAction = 'http://sp1/module.php/saml/sp/saml2-acs.php/hub4tests';
// $samlResponse2 = $I->grabValueFrom(['name' => 'SAMLResponse']);
// $I->sendPOST($formAction, ['SAMLResponse' => $samlResponse2], []);

// $I->showWebPage();


// $I->submitForm('form', ['SAMLResponse' => $samlResponse]);

// $I->click("Submit");


// $formAction = 'http://sp1/module.php/saml/sp/saml2-acs.php/hub4tests';
// $samlResponse2 = $I->grabValueFrom(['name' => 'SAMLResponse']);
// $I->amOnPage('http://sp1/module.php/core/authenticate.php?as=hub4tests');
$I->showWebPage();
