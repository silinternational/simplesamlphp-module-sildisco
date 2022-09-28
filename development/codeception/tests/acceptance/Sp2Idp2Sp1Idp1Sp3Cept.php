<?php 

$waitTime = 10;
$idp1Id =  '//*[@id="http://ssp-idp1.local:8085"]';

$I = new AcceptanceTester($scenario);
$I->wantTo("Ensure I can login to Sp2 through Idp2, must login to Sp1 if I choose Idp1, and don't need to login for Sp3.");

// Start at sp2. Go through hub to idp2
$I->amOnUrl('http://sp2/module.php/core/authenticate.php?as=hub4tests');
$I->waitForText("Enter your username and password", $waitTime);

$I->fillField('password', 'b');
$I->click('//*[@id="submit"]/td[3]/button');

$I->waitForText("@IDP2", $waitTime); // This should be the suffix on the NameId value

// Start at sp1
$I->amOnUrl('http://sp1/module.php/core/authenticate.php?as=hub4tests');

// Wait for redirect to the hub
$I->waitForElement($idp1Id, $waitTime);

$I->seeCurrentUrlMatches("~/module.php/sildisco/disco.php\?entityID=hub4tests~");

// Use idp1 and authenticate
$I->click($idp1Id . "/parent::*");

$I->waitForText("Enter your username and password", $waitTime);

$I->fillField('password', 'a');
$I->click('//*[@id="submit"]/td[3]/button');


$I->waitForText("test_admin@idp1.org", $waitTime);


// See that going to sp3 and selecting idp1 results in authentication without credentials
$I->amOnUrl('http://sp3/module.php/core/authenticate.php?as=hub4tests');
$I->seeCurrentUrlMatches("~/module.php/sildisco/disco.php\?entityID=hub4tests~");

// Ensure the SP's name shows up in the header bar
$I->waitForText('to continue to SP3', $waitTime);

$I->click($idp1Id . "/parent::*");

// Logout of both idp's
$I->click('Logout');
$I->waitForText("You have been logged out.", $waitTime);

$I->amOnUrl('http://sp2/module.php/core/authenticate.php?as=hub4tests');
$I->waitForText("@IDP2", $waitTime);
$I->click("Logout");
$I->waitForText("You have been logged out.", $waitTime);

