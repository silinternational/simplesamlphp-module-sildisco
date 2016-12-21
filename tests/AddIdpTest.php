<?php


class AddIdpTest extends PHPUnit_Framework_TestCase
{

    private static function getNameID($idp) {
        return [
            'saml:sp:IdP' => $idp,
            'saml:sp:NameID' => [
                [
                    'Format' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
                    'Value' => 'Tester1_Smith',
                    'SPNameQualifier' => 'http://ssp-hub-sp.local',
                ],
            ],
            'Attributes' => [],
            'metadataPath' => __DIR__ . '/fixtures/metadata/',
        ];
    }

    /**
     * Helper function to run the filter with a given configuration.
     *
     * @param array $config  The filter configuration.
     * @param array $request  The request state.
     * @return array  The state array after processing.
     */
    private static function processAddIdp2NameId(array $config, array $request)
    {
        $filter = new sspmod_sildisco_Auth_Process_AddIdp2NameId($config, NULL);
        $filter->process($request);
        return $request;
    }

    /*
     * Test with IdP metadata not having an IDPCode entry
     * @expectedException SimpleSAML_Error_Exception
     */
    public function testAddIdp2NameId_NoIDPCode()
    {
        $this->setExpectedException('SimpleSAML_Error_Exception');
        $config = [ 'test' => ['value1', 'value2'], ];
        $request = self::getNameID('idp-bare');

        self::processAddIdp2NameId($config, $request);
    }


    /*
     * Test with IdP metadata not having an IDPCode entry
     * @expectedException SimpleSAML_Error_Exception
     */
    public function testAddIdp2NameId_EmptyIDPCode()
    {
        $this->setExpectedException('SimpleSAML_Error_Exception');
        $config = [ 'test' => ['value1', 'value2'], ];
        $request = self::getNameID('idp-empty');
        self::processAddIdp2NameId($config, $request);
    }

    /*
     * Test with IdP metadata not having an IDPCode entry
     * @expectedException SimpleSAML_Error_Exception
     */
    public function testAddIdp2NameId_BadIDPCode()
    {
        $this->setExpectedException('SimpleSAML_Error_Exception');
        $config = [
            'test' => ['value1', 'value2'],
        ];
        $request = self::getNameID('idp-bad');
        self::processAddIdp2NameId($config, $request);
    }



    /*
     * Test with IdP metadata having a good IDPCode entry
     */
    public function testAddIdp2NameId_GoodString()
    {
        $config = ['test' => ['value1', 'value2']];
        $request = [
            'saml:sp:IdP' => 'idp-good',
            'saml:sp:NameID' => [
                'Tester1_SmithA',
                'Tester1_SmithB',
            ],
            'Attributes' => [],
            'metadataPath' => __DIR__ . '/fixtures/metadata/',
        ];

        $results = self::processAddIdp2NameId($config, $request);
        $expected = $results;
        $expected['saml:sp:NameID'][0] = 'Tester1_SmithA@idp-good';
        $expected['saml:sp:NameID'][1] = 'Tester1_SmithB@idp-good';

        $this->assertEquals($expected, $results);
    }
    /*
     * Test with IdP metadata having a good IDPCode entry
     */
    public function testAddIdp2NameId_GoodArray()
    {
        $config = ['test' => ['value1', 'value2']];
        $state = [
            'saml:sp:IdP' => 'idp-good',
            'saml:sp:NameID' => [
                [
                    'Format' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
                    'Value' => 'Tester1_SmithA',
                    'SPNameQualifier' => 'http://ssp-hub-sp.local',
                ],
                [
                    'Format' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:transient',
                    'Value' => 'Tester1_SmithB',
                    'SPNameQualifier' => 'http://ssp-hub-sp.local',
                ],
            ],
            'Attributes' => [],
            'metadataPath' => __DIR__ . '/fixtures/metadata/',
        ];

        $results = self::processAddIdp2NameId($config, $state);
        $expected = $results;
        $expected['saml:sp:NameID'][0]['Value'] = 'Tester1_SmithA@idp-good';
        $expected['saml:sp:NameID'][1]['Value'] = 'Tester1_SmithB@idp-good';

        $this->assertEquals($expected, $results);
    }

}