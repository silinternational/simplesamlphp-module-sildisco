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

        $this->format = Null;
        if ( ! empty($config[self::FORMAT_KEY])) {
            $this->format = (string) $config[self::FORMAT_KEY];
        }
    }

    /**
     * Apply filter to copy attributes.
     *
     * @param array &$state  The current state array
     */
    public function process(&$state) {
        assert('is_array($state)');
//        assert('is_array($request)');
//        assert('array_key_exists("Attributes", $request)');


        $samlIDP = $state[self::IDP_KEY];

        // Get the potential IDPs from idp remote metadata
        $metadataPath = __DIR__ . '/../../../../../metadata';

        // If a unit test sends a different metadataPath, use it
        if (isset($state['metadataPath'])) {
            $metadataPath = $state['metadataPath'];
        }
        $idpEntries = \Sil\SspUtils\Metadata::getIdpMetadataEntries($metadataPath);

        // Get the IDPNamespace or else just use the IDP's entity ID
        $IDPNamespace = $samlIDP;
        $idpEntry = $idpEntries[$samlIDP];

        // The IDP metadata must have an IDPNamespace entry
        if (isset($idpEntry[self::IDP_CODE_KEY]) && is_string($idpEntry[self::IDP_CODE_KEY])) {
            if ( preg_match("/^[A-Za-z0-9_-]+$/", $idpEntry[self::IDP_CODE_KEY])) {
                $IDPNamespace = $idpEntry[self::IDP_CODE_KEY];
            }
        }

        // Get the SP's entity id
        $spEntityId = $state['SPMetadata']['entityid'];

        // Get the current user's common name attribute
        $attributes =& $state['Attributes'];
        $oid4cn = 'urn:oid:2.5.4.3';
        $cnKey = 'cn';
        $user = 'Unnamed_User';

        if (!empty($attributes[$oid4cn])) {
            $user = $attributes[$oid4cn];
        } else if (!empty($attributes[$cnKey])) {
            $user = $attributes($cnKey);
        }

        // Get the current datetime
        $datetime = date("Y-m-d H:i:s");

        $sdk = new Aws\Sdk([
            'endpoint'   => $this->awsEndpoint,
            'region'   => $this->awsRegion,
            'version'  => 'latest'
        ]);

        $dynamodb = $sdk->createDynamoDb();
        $marshaler = new Marshaler();


        $id = uniqid();

        $item = $marshaler->marshalJson('
            {
                "ID": "' . $id . '",
                "IDP": "' . $IDPNamespace . '",
                "SP": "' . $spEntityId . '",
                "User": "' . $user . '",
                "Time": "' . $datetime . '"
            }'
        );

        $params = [
            'TableName' => $this->TableName,
            'Item' => $item,
        ];

        try {
            $result = $dynamodb->putItem($params);
        } catch (DynamoDbException $e) {
            echo "Unable to add item:\n";
            echo $e->getMessage() . "\n";
        }
    }

}