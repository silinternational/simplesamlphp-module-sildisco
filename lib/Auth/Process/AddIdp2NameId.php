<?php


use Sil\SspUtils\Metadata;

/**
 * Attribute filter for prefixing group names
 *
 */
class sspmod_sildisco_Auth_Process_AddIdp2NameId extends SimpleSAML_Auth_ProcessingFilter {

    const IDP_CODE_KEY = 'IDPCode'; // the metadata key for the IDP's code (i.e. short name)

    const DELIMITER = '@'; // The symbol between the NameID proper and the Idp code.


    /**
     * The attribute we should use as the NameID.
     *
     * @var string
     */
    private $attribute;

    /**
     * The format of this NameID.
     *
     * This property must be initialized the subclass.
     *
     * @var string
     */
    protected $format;

    /**
     * Initialize this filter, parse configuration.
     *
     * @param array $config Configuration information about this filter.
     * @param mixed $reserved For future use.
     *
     * @throws SimpleSAML_Error_Exception If the required options 'Format' or 'attribute' are missing.
     */
    public function __construct($config, $reserved)
    {
        parent::__construct($config, $reserved);
        assert('is_array($config)');

        if (!isset($config['Format'])) {
            throw new SimpleSAML_Error_Exception("AttributeNameID: Missing required option 'Format'.");
        }
        $this->format = (string) $config['Format'];

        if (!isset($config['attribute'])) {
            throw new SimpleSAML_Error_Exception("AttributeNameID: Missing required option 'attribute'.");
        }
        $this->attribute = (string) $config['attribute'];
    }



    /**
     * Get the NameID value.
     *
     * @param array $state The state array.
     * @return string|null The NameID value.
     */
    protected function getValue(array &$state)
    {

        if (!isset($state['Attributes'][$this->attribute]) || count($state['Attributes'][$this->attribute]) === 0) {
            SimpleSAML_Logger::warning(
                'Missing attribute '.var_export($this->attribute, true).
                ' on user - not generating attribute NameID.'
            );
            return null;
        }
        if (count($state['Attributes'][$this->attribute]) > 1) {
            SimpleSAML_Logger::warning(
                'More than one value in attribute '.var_export($this->attribute, true).
                ' on user - not generating attribute NameID.'
            );
        }
        $value = array_values($state['Attributes'][$this->attribute]); // just in case the first index is no longer 0
        $value = $value[0];
        return $value;
    }

    /**
     * Apply filter to copy attributes.
     *
     * @param array &$state  The current state array
     */
    public function process(&$state) {
        assert('is_array($state)');
        assert('is_string($this->format)');
        assert('array_key_exists("Attributes", $state)');

        $value = $this->getValue($state);
        if ($value === NULL) {
            return;
        }

        // Get the potential IDPs from idp remote metadata
        $metadataPath = __DIR__ . '/../../../../../metadata';
        $idpEntries = \Sil\SspUtils\Metadata::getIdpMetadataEntries($metadataPath);
        
        $newGroups = array();
        $samlIDP = $state["saml:sp:IdP"];

        $idpEntry = $idpEntries[$samlIDP];

        if ( ! isset($idpEntry[self::IDP_CODE_KEY])) {
            throw new SimpleSAML_Error_Exception("AddIdp2NameId: Missing required metadata entry: " .
                                                 self::IDP_CODE_KEY . ".");
        }

        if ( ! is_string($idpEntry[self::IDP_CODE_KEY])) {
            throw new SimpleSAML_Error_Exception("AddIdp2NameId: Required metadata " .
                "entry must be a string: " .  self::IDP_CODE_KEY . ".");
        }

        $idp = $idpEntry[self::IDP_CODE_KEY];

        $state['saml:NameID'][$this->format] = $value . self::DELIMITER . $idp;
    }

}

?>
