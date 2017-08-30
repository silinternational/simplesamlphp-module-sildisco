<?php 

$waitTime = 10;
$idp2Id =  '//*[@id="http://ssp-hub-idp2.local:8086"]';

$I = new AcceptanceTester($scenario);
$I->wantTo('Ensure I can login to Sp2 through Idp2, get discovery page for Sp1, and must login to Sp3 through Idp1.');

// Start at sp2. Go through hub to idp2
$I->amOnUrl('http://sp2/module.php/core/authenticate.php?as=hub4tests');
$I->waitForText("Enter your username and password", $waitTime);

$I->fillField('password', 'b');
$I->click('//*[@id="submit"]/td[3]/button');

$I->waitForText("@IDP2", $waitTime);

// Start at sp1
$I->amOnUrl('http://sp1/module.php/core/authenticate.php?as=hub4tests');

// Wait for redirect to the hub
$I->waitForElement($idp2Id, $waitTime);
$I->seeCurrentUrlMatches("~/module.php/sildisco/disco.php\?entityID=hub4tests~");

// Use idp2 but already Authenticated
$I->click($idp2Id . "/parent::*");

$I->waitForText("@IDP2", $waitTime);


// See that going to sp3 requires login
$I->amOnUrl('http://sp3/module.php/core/authenticate.php?as=hub4tests');

$I->waitForText("Enter your username and password", $waitTime);

$I->fillField('password', 'a');
$I->click('//*[@id="submit"]/td[3]/button');

$I->waitForText("test_admin@idp1.org", $waitTime);

// Logout of both idp's
$I->click('Logout');
$I->waitForText("You have been logged out.", $waitTime);

$I->amOnUrl('http://sp2/module.php/core/authenticate.php?as=hub4tests');
$I->waitForText("@IDP2", $waitTime);
$I->click("Logout");
$I->waitForText("You have been logged out.", $waitTime);

