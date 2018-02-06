<?php 

/*
 * Note: Once this test is run, the beta_tester cookie is set.  So, the test will
 * fail on a second run, unless the container is killed and restarted.
 */

$waitTime = 15;
$idp1Id =  '//*[@id="http://ssp-hub-idp.local:8085"]';
$idp3Id =  '//*[@id="http://ssp-hub-idp3.local:8087"]';

$I = new AcceptanceTester($scenario);
$I->wantTo("Ensure I don't see IdP 3 at first, but after I go to the Beta Tester page I can see and login through IdP 3.");

// Start at sp1
$I->amOnUrl('http://sp1');
$I->waitForText('About SimpleSAMLphp', $waitTime);

$I->click('Authentication');
$I->click('Test configured authentication sources');
$I->waitForText('Test authentication sources', $waitTime);
$I->makeScreenshot('hub4tests_1');

// Go to the hub
$I->click('hub4tests');
$I->makeScreenshot('hub4tests_2');
$I->waitForElement($idp1Id, $waitTime);

$I->seeCurrentUrlMatches("~/module.php/sildisco/disco.php\?entityID=hub4tests~");

// I make sure Idp3 is disabled
$I->seeInSource('IdP 3 coming soon');

// Go to beta_tester url
$I->amOnUrl('http://hub4tests/module.php/sildisco/betatest.php');
$I->makeScreenshot('betatest');
$I->waitForText('beta', $waitTime);

// Go back to hub
$I->amOnUrl('http://sp1/module.php/core/authenticate.php?as=hub4tests');
$I->makeScreenshot('hub4tests_3');
$I->waitForElement($idp1Id, $waitTime);

// Use idp3 for Authentication
$I->click($idp3Id . "/parent::*");
$I->makeScreenshot('login');

$I->waitForText("Enter your username and password", $waitTime);

$I->fillField('password', 'c');
$I->click('//*[@id="submit"]/td[3]/button');
$I->makeScreenshot('post_login');

$I->waitForText("http://ssp-hub-sp.local", $waitTime);


// Logout of IDP3
$I->click("Logout");
$I->waitForText("You have been logged out.", $waitTime);
