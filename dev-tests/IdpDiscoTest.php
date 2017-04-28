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
        sspmod_sildisco_IdPDisco::enableBetaEnabled($idpList);
        $expected = [];
        $this->assertEquals($expected, $idpList);
    }

    public function testEnableBetaEnabledNoChange()
    {
        $isBetaEnabled = true;
        $enabledKey = sspmod_sildisco_IdPDisco::$enabledMdKey;
        $idpList = [
            'idp1' => [$enabledKey => false],
            'idp2' => [$enabledKey => true],
        ];
        $expected = $idpList;

        sspmod_sildisco_IdPDisco::enableBetaEnabled($idpList, $isBetaEnabled);
        $this->assertEquals($expected, $idpList);
    }

    public function testEnableBetaEnabledChange()
    {
        $isBetaEnabled = true;
        $enabledKey = sspmod_sildisco_IdPDisco::$enabledMdKey;
        $betaEnabledKey = sspmod_sildisco_IdPDisco::$betaEnabledMdKey;
        $idpList = [
            'idp1' => [$enabledKey => false],
            'idp2' => [$enabledKey => true, $betaEnabledKey => true],
            'idp3' => [$enabledKey => false, $betaEnabledKey => true],
            'idp4' => [$enabledKey => true],
        ];
        $expected = $idpList;
        $expected['idp3'][$betaEnabledKey] = true;

        sspmod_sildisco_IdPDisco::enableBetaEnabled($idpList, $isBetaEnabled);
        $this->assertEquals($expected, $idpList);
    }

}