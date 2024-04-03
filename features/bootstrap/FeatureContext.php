<?php

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Testwork\Tester\Result\TestResult;
use DMore\ChromeDriver\ChromeDriver;
use Webmozart\Assert\Assert;

class FeatureContext extends MinkContext
{
    private const SP_LOGIN_PATH = '/module.php/core/authenticate.php?as=hub4tests';
    private const SP1_LOGIN_PAGE = 'http://sp1/' . self::SP_LOGIN_PATH;
    private const SP2_LOGIN_PAGE = 'http://sp2/' . self::SP_LOGIN_PATH;
    private const SP3_LOGIN_PAGE = 'http://sp3/' . self::SP_LOGIN_PATH;

    /** @var Session */
    private $session;

    public function __construct()
    {
        $driver = new ChromeDriver('http://chrome:9222', null, 'http://ssp-sp1');
        $this->session = new Session($driver);
        $mink = new Mink(['default' => $this->session]);
        $mink->setDefaultSessionName('default');
        $this->setMink($mink);
        // See http://mink.behat.org/en/latest/guides/session.html for docs.
        $this->session->start();
    }

    /** @AfterStep */
    public function afterStep(AfterStepScope $scope)
    {
        if ($scope->getTestResult()->getResultCode() === TestResult::FAILED) {
            $this->showPageDetails();
        }
    }

    private function showPageDetails()
    {
        echo '[' . $this->session->getStatusCode() . '] ';
        $this->printLastResponse();
    }

    /**
     * @When I click on :linkText
     */
    public function iClickOn($linkText)
    {
        $this->clickLink($linkText);
    }

    private function logInAs(string $username, string $password)
    {
        $this->fillField('username', $username);
        $this->fillField('password', $password);
        $this->pressButton('Login');
    }

    /**
     * @When I click on the :idpName tile
     */
    public function iClickOnTheTile($idpName)
    {
        $page = $this->session->getPage();
        $idpTileTitle = sprintf('Login with your %s identity account', $idpName);
        $idpTile = $page->find(
            'css',
            sprintf('div[title="%s"]', $idpTileTitle)
        );
        Assert::notNull($idpTile, 'Failed to find ' . $idpName . ' tile' . ' looking for ' . $idpTileTitle);
        $button = $idpTile->find('css', 'button');
        Assert::notNull($button, 'Failed to find button for ' . $idpName);
        $button->press();
    }

    /**
     * @When I go to the :sp login page
     */
    public function iGoToTheSpLoginPage($sp)
    {
        switch ($sp) {
            case 'SP1':
                $this->visit(self::SP1_LOGIN_PAGE);
                break;
            case 'SP2':
                $this->visit(self::SP2_LOGIN_PAGE);
                break;
            case 'SP3':
                $this->visit(self::SP3_LOGIN_PAGE);
                break;
        }
    }

    /**
     * @When I login using password :password
     */
    public function iLoginUsingPassword($password)
    {
        $this->logInAs('admin', $password);
    }

    /**
     * @Given I have authenticated with IDP1 for :sp
     */
    public function iHaveAuthenticatedWithIdp1($sp)
    {
        $this->iGoToTheSpLoginPage($sp);
        $this->iClickOnTheTile('IdP 1');
        $this->logInAs('admin', 'a');
    }

    /**
     * @Given I have authenticated with IDP2 for :sp
     */
    public function iHaveAuthenticatedWithIdp2($sp)
    {
        $this->iGoToTheSpLoginPage($sp);
        if ($sp != "SP2") { // SP2 only has IDP2 in its IDPList
            $this->iClickOnTheTile('IdP 2');
        }
        $this->logInAs('admin', 'b');
    }

    /**
     * @When I log out of IDP1
     */
    public function iLogOutOfIdp1()
    {
        $this->iGoToTheSpLoginPage('SP3');
        $this->iClickOnTheTile('IdP 1');
        $this->logInAs('admin', 'a');
        $this->clickLink('Logout');
        $this->assertPageContainsText('You have been logged out.');
    }

    /**
     * @When I log out of IDP2
     */
    public function iLogOutOfIdp2()
    {
        $this->iGoToTheSpLoginPage('SP2');
        $this->logInAs('admin', 'b');
        $this->clickLink('Logout');
        $this->assertPageContainsText('You have been logged out.');
    }

    /**
     * @Then I should see the metadata in XML format
     */
    public function iShouldSeeTheMetadataInXmlFormat()
    {
        $xml = $this->getSession()->getDriver()->getContent();
        assert(str_contains($xml, 'entityID="hub4tests"'));
    }

}

