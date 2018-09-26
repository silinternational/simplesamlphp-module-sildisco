<?php

use Aws\DynamoDb\Marshaler;

/**
 * This Auth Proc logs information about each successful login to an AWS Dynamodb table.
 *
 *  It requires the following configs
 *   'DynamoEndpoint' ex. http://arn:aws:dynamodb:us-east-1:1234567
 *   'DynamoRegion' ex. 'us-east-1'
 *   'DynamoLogTable' ex. 'sildisco_dev_user-log'
 *
 */
class sspmod_sildisco_Auth_Process_LogUser extends SimpleSAML_Auth_ProcessingFilter
{
    const IDP_KEY = "saml:sp:IdP"; // the key that points to the entity id in the state

    // the metadata key for the IDP's Namespace code (i.e. short name)
    const IDP_CODE_KEY = 'IDPNamespace';

    const DYNAMO_ENDPOINT_KEY = 'DynamoEndpoint';

    const DYNAMO_REGION_KEY = 'DynamoRegion';

    const DYNAMO_LOG_TABLE_KEY = 'DynamoLogTable';

    const SECONDS_PER_YEAR = 31536000; // 60 * 60 * 24 * 365


    // The host of the aws dynamodb
    private $dynamoEndpoint;

    // The region of the aws dynamodb
    private $dynamoRegion;

    // The name of the aws dynamodb table that stores the login data
    private $dynamoLogTable;

    /**
     * Initialize this filter, parse configuration.
     *
     * @param array $config  Configuration information about this filter.
     * @param mixed $reserved  For future use.
     */
    public function __construct($config, $reserved) {
        parent::__construct($config, $reserved);
        assert(is_array($config));

        if (! empty($config[self::DYNAMO_ENDPOINT_KEY])) {
            $this->dynamoEndpoint = $config[self::DYNAMO_ENDPOINT_KEY];
        }

        if (! empty($config[self::DYNAMO_REGION_KEY])) {
            $this->dynamoRegion = $config[self::DYNAMO_REGION_KEY];
        }

        if (! empty($config[self::DYNAMO_LOG_TABLE_KEY])) {
            $this->dynamoLogTable = $config[self::DYNAMO_LOG_TABLE_KEY];
        }

    }

    /**
     * Log info for a user's login to Dyanmodb
     *
     * @param array &$state  The current state array
     */
    public function process(&$state) {
        if (! $this->configsAreValid()) {
            return;
        }

        assert(is_array($state));

        // Get the SP's entity id
        $spEntityId = "SP entity ID not available";
        if (! empty($state['saml:sp:State']['SPMetadata']['entityid'])) {
            $spEntityId = $state['saml:sp:State']['SPMetadata']['entityid'];
        }

        $sdk = new Aws\Sdk([
            'endpoint'   => $this->dynamoEndpoint,
            'region'   => $this->dynamoRegion,
            'version'  => 'latest'
        ]);

        $dynamodb = $sdk->createDynamoDb();
        $marshaler = new Marshaler();

        $userAttributes = $this->getUserAttributes($state);

        $logContents = array_merge(
            $userAttributes,
            [
                "ID" => uniqid(),
                "IDP" => $this->getIdp($state),
                "SP" => $spEntityId,
                "Time" => date("Y-m-d H:i:s"),
                "ExpiresAt" => time() + self::SECONDS_PER_YEAR,
            ]
        );

        $logJson = json_encode($logContents);

        $item = $marshaler->marshalJson($logJson);

        $params = [
            'TableName' => $this->dynamoLogTable,
            'Item' => $item,
        ];

        try {
            $result = $dynamodb->putItem($params);
        } catch (Exception $e) {
            SimpleSAML\Logger::error("Unable to add item: ". $e->getMessage());
        }
    }

    private function configsAreValid() {
        $msg = ' config value not provided to LogUser.';
        if (empty($this->dynamoEndpoint)) {
            SimpleSAML\Logger::error(self::DYNAMO_ENDPOINT_KEY . $msg);
            return false;
        }

        if (empty($this->dynamoRegion)) {
            SimpleSAML\Logger::error(self::DYNAMO_REGION_KEY . $msg);
            return false;
        }

        if (empty($this->dynamoLogTable)) {
            SimpleSAML\Logger::error(self::DYNAMO_LOG_TABLE_KEY . $msg);
            return false;
        }

        return true;
    }

    private function getIdp(&$state) {
        if (empty($state[self::IDP_KEY])) {
            return 'No IDP available';
        }

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

        // If the IDPNamespace entry is a string, use it
        if (isset($idpEntry[self::IDP_CODE_KEY]) && is_string($idpEntry[self::IDP_CODE_KEY])) {
            return $idpEntry[self::IDP_CODE_KEY];
        }

        // Default, use the idp's entity ID
        return $samlIDP;
    }

    // Get the current user's common name attribute and/or eduPersonPrincipalName and/or employeeNumber
    private function getUserAttributes($state) {
        $attributes = $state['Attributes'];

        $cn = $this->getAttributeFrom($attributes, 'urn:oid:2.5.4.3', 'cn');

        $eduPersonPrincipalName = $this->getAttributeFrom(
            $attributes,
            'urn:oid:1.3.6.1.4.1.5923.1.1.1.6',
            'eduPersonPrincipalName'
        );

        $employeeNumber = $this->getAttributeFrom(
            $attributes,
            'urn:oid:2.16.840.1.113730.3.1.3',
            'employeeNumber'
        );

        $userAttrs = [];

        $userAttrs = $this->addUserAttribute($userAttrs, "CN", $cn);
        $userAttrs = $this->addUserAttribute($userAttrs, "EduPersonPrincipalName", $eduPersonPrincipalName);
        $userAttrs = $this->addUserAttribute($userAttrs, "EmployeeNumber", $employeeNumber);

        return $userAttrs;
    }

    private function getAttributeFrom($attributes, $oidKey, $friendlyKey) {
        if (!empty($attributes[$oidKey])) {
            return $attributes[$oidKey][0];
        }

        if (!empty($attributes[$friendlyKey])) {
            return $attributes[$friendlyKey][0];
        }

        return '';
    }

    // Dynamodb seems to complain when a value is an empty string.
    // This ensures that only attributes with a non empty value get included.
    private function addUserAttribute($attributes, $attrKey, $attr) {
        if (!empty($attr)) {
            $attributes[$attrKey] = $attr;
        }

        return $attributes;
    }

}
