<?php
//namespace Sil\SilDiscoTests;

//include __DIR__ . '../../../../vendor/autoload.php';

//use PHPUnit\Framework\TestCase;
//use Sil\SspUtils\Utils;
//use Sil\SspUtils\Metadata;

//use sspmod_sildisco_Auth_Process_AddIdp2NameId;

class AuthProcTest extends PHPUnit_Framework_TestCase
{

    /**
     * Helper function to run the filter with a given configuration.
     *
     * @param array $config  The filter configuration.
     * @param array $request  The request state.
     * @return array  The state array after processing.
     */
    private static function processFilter(array $config, array $request)
    {
        $filter = new sspmod_sildisco_Auth_Process_AddIdp2NameId($config, NULL);
        $filter->process($request);
        return $request;
    }

    /*
     *
     *
     */
    public function testAddIdp2NameId_NoNameId()
    {
        $config = array(
            'test' => array('value1', 'value2'),
        );
        $request = array(
            'Attributes' => array(),
        );

        $expected = ['stub_expected'];

        $results = self::processFilter($config, $request);

        $this->assertEquals($expected, $results);
    }

}