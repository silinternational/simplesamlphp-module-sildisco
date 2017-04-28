<?php

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../vendor/simplesamlphp/simplesamlphp/modules/sildisco/lib/IdPDisco.php';

use PHPUnit\Framework\TestCase;

class AnnouncementTest extends TestCase
{

    /**
     * Ensure the /data/ssp-announcement.php file can be included without an error
     */
    public function testEnableBetaEnabledEmpty()
    {
        $idpList = [];
        $results = sspmod_sildisco_IdPDisco::enableBetaEnabled($idpList);
        $expected = [];
        $this->assertEquals($expected, $results);
    }

    public function testEnableBetaEnabledNoChange()
    {
        $isBetaEnabled = 1;
        $enabledKey = sspmod_sildisco_IdPDisco::$enabledMdKey;
        $idpList = [
            'idp1' => [$enabledKey => false],
            'idp2' => [$enabledKey => true],
        ];
        $expected = $idpList;

        $results = sspmod_sildisco_IdPDisco::enableBetaEnabled($idpList, $isBetaEnabled);
        $this->assertEquals($expected, $results);
    }

    public function testEnableBetaEnabledChange()
    {
        $isBetaEnabled = 1;
        $enabledKey = sspmod_sildisco_IdPDisco::$enabledMdKey;
        $betaEnabledKey = sspmod_sildisco_IdPDisco::$betaEnabledMdKey;
        $idpList = [
            'idp1' => [$enabledKey => false],
            'idp2' => [$enabledKey => true, $betaEnabledKey => true],
            'idp3' => [$enabledKey => false, $betaEnabledKey => true],
            'idp4' => [$enabledKey => true],
        ];
        $expected = $idpList;
        $expected['idp3'][$enabledKey] = true;

        $results = sspmod_sildisco_IdPDisco::enableBetaEnabled($idpList, $isBetaEnabled);
        $this->assertEquals($expected, $results);
    }

}