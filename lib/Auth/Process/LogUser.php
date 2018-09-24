<?php

use Sil\SspUtils\Metadata;

use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;

/**
 *
 */
class sspmod_sildisco_Auth_Process_LogUser extends SimpleSAML_Auth_ProcessingFilter
{
    const IDP_KEY = "saml:sp:IdP"; // the key that points to the entity id in the state

    // the metadata key for the IDP's Namespace code (i.e. short name)
    const IDP_CODE_KEY = 'IDPNamespace';

    // The host of the aws dynamodb
    private $awsEndpoint;

    // The region of the aws dynamodb
    private $awsRegion;

    // The name of the aws dynamodb table that stores the login data
    private $dbTableName;

    /**
     * Initialize this filter, parse configuration.
     *
     * @param array $config  Configuration information about this filter.
     * @param mixed $reserved  For future use.
     */
    public function __construct($config, $reserved) {
        parent::__construct($config, $reserved);
        assert('is_array($config)');

        if (isset($config['AWSEndpoint'])) {
            $this->awsEndpoint = $config['AWSEndpoint'];
        } else {
            $this->awsEndpoint = 'http://aws-endpoint-missing:8000';
        }

        if (isset($config['AWSRegion'])) {
            $this->awsRegion = $config['AWSRegion'];
        } else {
            $this->awsRegion = 'us-east-1';
        }

        if (isset($config['DBTableName'])) {
            $this->dbTableName = $config['DBTableName'];
        } else {
            $this->dbTableName = 'sildisco_default_user-log';
        }

    }

    /**
     * Apply filter to copy attributes.
     *
     * @param array &$state  The current state array
     */
    public function process(&$state) {
        assert('is_array($state)');
//        assert('array_key_exists("Attributes", $state)');

        $idp = $this->getIdp($state);

        // Get the SP's entity id
        $spEntityId = $state['saml:sp:State']['SPMetadata']['entityid'];

        $user = $this->getUser($state);

        // Get the current datetime
        $datetime = date("Y-m-d H:i:s");

        $sdk = new Aws\Sdk([
            'endpoint'   => $this->awsEndpoint,
            'region'   => $this->awsRegion,
            'version'  => 'latest'
        ]);

        $dynamodb = $sdk->createDynamoDb();
        $marshaler = new Marshaler();

        $logJson = '
            {
                "ID": "' . uniqid() . '",
                "IDP": "' . $idp . '",
                "SP": "' . $spEntityId . '",
                "User": "' . $user . '",
                "Time": "' . $datetime . '"
            }';

        $item = $marshaler->marshalJson($logJson);

        $params = [
            'TableName' => $this->dbTableName,
            'Item' => $item,
        ];

        try {
            $result = $dynamodb->putItem($params);
        } catch (DynamoDbException $e) {
            echo "Unable to add item:\n";
            echo $e->getMessage() . "\n";
        }
    }

    private function getIdp(&$state) {
        $samlIDP = $state[self::IDP_KEY];

        // Get the potential IDPs from idp remote metadata
        $metadataPath = __DIR__ . '/../../../../../metadata';

        // If a unit test sends a different metadataPath, use it
        if (isset($state['metadataPath'])) {
            $metadataPath = $state['metadataPath'];
        }
        $idpEntries = \Sil\SspUtils\Metadata::getIdpMetadataEntries($metadataPath);

        // Get the IDPNamespace or else just use the IDP's entity ID
        $idpEntry = $idpEntries[$samlIDP];

        // The IDPNamespace entry is close to being alphanumeric, use it
        if (isset($idpEntry[self::IDP_CODE_KEY]) && is_string($idpEntry[self::IDP_CODE_KEY])) {
            if ( preg_match("/^[A-Za-z0-9_-]+$/", $idpEntry[self::IDP_CODE_KEY])) {
                return $idpEntry[self::IDP_CODE_KEY];
            }
        }
        // Default, just use the idp's entity_id
        return $samlIDP;
    }

    private function getUser($state) {
        // Get the current user's common name attribute or otherwise eduPersonPrincipalName
        $attributes = $state['Attributes'];

        $oidForCn = 'urn:oid:2.5.4.3';
        $cnKey = 'cn';
        $oidForEduPersonPrincipalName = 'urn:oid:1.3.6.1.4.1.5923.1.1.1.6';
        $eduPPNKey = 'eduPersonPrincipalName';


        if (!empty($attributes[$oidForCn])) {
            return $attributes[$oidForCn][0];
        }

        if (!empty($attributes[$cnKey])) {
            return $attributes[$cnKey][0];
        }

        if (!empty($attributes[$oidForEduPersonPrincipalName])) {
            return $attributes[$oidForEduPersonPrincipalName][0];
        }

        if (!empty($attributes[$eduPPNKey])) {
            return $attributes[$eduPPNKey][0];
        }

        return 'Unnamed_User';
    }

}
