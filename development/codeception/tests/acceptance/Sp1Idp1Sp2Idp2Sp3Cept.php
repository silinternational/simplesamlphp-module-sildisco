<?php 

$waitTime = 10;

$idp1Id =  '//*[@id="http://ssp-idp1.local:8085"]';
$spHomePath = '/module.php/core/frontpage_welcome.php';

$I = new AcceptanceTester($scenario);
$I->wantTo('Wait for containers and then ensure I can login to Sp1 through Idp1, must login to Sp2 through Idp2 and am already logged in for Sp3.');

// Start at sp1.  If this fails, maybe the containers didn't have enough time to spin up.
$I->amOnUrl('http://sp1' . $spHomePath);
$I->waitForText('Congratulations', $waitTime);

$I->click('Authentication');
$I->click('Test configured authentication sources');
$I->waitForText('Test authentication sources', $waitTime);

// Go to the hub
$I->click('hub4tests');
$I->waitForElement($idp1Id, $waitTime);

$I->seeCurrentUrlMatches("~/module.php/sildisco/disco.php\?entityID=hub4tests~");

// Ensure the SP's name shows up in the header bar
$I->waitForText('to continue to SP1', $waitTime);

// Use idp1 for Authentication
$I->click($idp1Id . "/parent::*");
$I->waitForText("Enter your username and password", $waitTime);

$I->fillField('password', 'a');
$I->click('//*[@id="submit"]/td[3]/button');

$I->waitForText("@IDP1", $waitTime); // This should be the suffix on the NameId value


// Start at sp2. Go through hub to idp2
$I->amOnUrl('http://sp2/module.php/core/authenticate.php?as=hub4tests');

$I->waitForText("Enter your username and password", $waitTime);

$I->fillField('password', 'b');
$I->click('//*[@id="submit"]/td[3]/button');

$I->waitForText("@IDP2", $waitTime); // This should be the suffix on the NameId value

// See that going to sp3 and selecting idp1 results in authentication without credentials
$I->amOnUrl('http://sp3/module.php/core/authenticate.php?as=hub4tests');


$I->seeCurrentUrlMatches("~/module.php/sildisco/disco.php\?entityID=hub4tests~");

// Ensure the SP's name shows up in the header bar
$I->waitForText('to continue to SP3', $waitTime);

$I->click($idp1Id . "/parent::*");

$I->waitForText("test_admin@idp1.org", $waitTime);

// Logout of both IDP's
$I->click("Logout");
$I->waitForText("You have been logged out.", $waitTime);


$I->amOnUrl('http://sp2/module.php/core/authenticate.php?as=hub4tests');
$I->waitForText("@IDP2", $waitTime); // This should be the suffix on the NameId value
$I->click("Logout");
$I->waitForText("You have been logged out.", $waitTime);
