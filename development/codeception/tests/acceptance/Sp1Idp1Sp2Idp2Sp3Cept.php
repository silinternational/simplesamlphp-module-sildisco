<?php 

$waitTime = 10;

$I = new AcceptanceTester($scenario);
$I->wantTo('Ensure I can login to Sp1 through Idp1, must login to Sp2 through Idp2 and am already logged in for Sp3.');

// Give a little extra time for containers to come up.
$I->wait(20);

// Start at sp1
$I->amOnUrl('http://sp1');
$I->waitForText('About SimpleSAMLphp', $waitTime);

$I->click('Authentication');
$I->click('Test configured authentication sources');
$I->waitForText('Test authentication sources', $waitTime);

// Go to the hub
$I->click('hub4tests');
$I->waitForText('IdP 1', $waitTime);

$I->seeCurrentUrlMatches("~/module.php/sildisco/disco.php\?entityID=hub4tests~");

// Use idp1 for Authentication
$I->click(["name" => "idp_http://ssp-hub-idp.local:8085"]);
$I->waitForText("Enter your username and password", $waitTime);

$I->fillField('password', 'a');
$I->click('//*[@id="regularsubmit"]/td[3]/button');

$I->waitForText("test_admin@idp1.org", $waitTime);


// Start at sp2. Go through hub to idp2
$I->amOnUrl('http://sp2/module.php/core/authenticate.php?as=hub4tests');
$I->waitForText("Enter your username and password", $waitTime);

$I->fillField('password', 'b');
$I->click('//*[@id="regularsubmit"]/td[3]/button');

$I->waitForText("@IDP2", $waitTime);

// See that going to sp3 results in immediate authentication to idp1
$I->amOnUrl('http://sp3/module.php/core/authenticate.php?as=hub4tests');
$I->waitForText("test_admin@idp1.org", $waitTime);

// Logout of both IDP's
$I->click("Logout");
$I->waitForText("You have been logged out.", $waitTime);


$I->amOnUrl('http://sp2/module.php/core/authenticate.php?as=hub4tests');
$I->waitForText("@IDP2", $waitTime);
$I->click("Logout");
$I->waitForText("You have been logged out.", $waitTime);
