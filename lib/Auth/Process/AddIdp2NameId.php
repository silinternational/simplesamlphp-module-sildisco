<?php


use Sil\SspUtils\Metadata;

/**
 * Attribute filter for appending IDPNamespace to the NameID.
 * The IdP must have a IDPNamespace entry in its metadata.
 *
 * Also, for this to work, the SP needs to include a line in its 
 * authsources.php file in the IdP's entry ...
 *   'NameIDPolicy' => "urn:oasis:names:tc:SAML:2.0:nameid-format:persistent",
 *
 */
class sspmod_sildisco_Auth_Process_AddIdp2NameId extends SimpleSAML_Auth_ProcessingFilter {

    const IDP_KEY = "saml:sp:IdP"; // the key that points to the entity id in the state

    // the metadata key for the IDP's Namespace code (i.e. short name)
    const IDP_CODE_KEY = 'IDPNamespace'; 

    const DELIMITER = '@'; // The symbol between the NameID proper and the Idp code.

    const SP_NAMEID_ATTR = 'saml:sp:NameID'; // The key for the NameID

    const VALUE_KEY = 'Value';  // The value key for the NamedID entry

    const ERROR_PREFIX = "AddIdp2NameId: "; // Text to go at the beginning of error messages

    const FORMAT_KEY = 'Format';

    /**
     * What NameQualifier should be used.
     * Can be one of:
     *  - a string: The qualifier to use.
     *  - FALSE: Do not include a NameQualifier. This is the default.
     *  - TRUE: Use the IdP entity ID.
     *
     * @var string|bool
     */
    private $nameQualifier;


    /**
     * What SPNameQualifier should be used.
     * Can be one of:
     *  - a string: The qualifier to use.
     *  - FALSE: Do not include a SPNameQualifier.
     *  - TRUE: Use the SP entity ID. This is the default.
     *
     * @var string|bool
     */
    private $spNameQualifier;


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
     * @param array $config  Configuration information about this filter.
     * @param mixed $reserved  For future use.
     */
    public function __construct($config, $reserved) {
        parent::__construct($config, $reserved);
        assert('is_array($config)');

        if (isset($config['NameQualifier'])) {
            $this->nameQualifier = $config['NameQualifier'];
        } else {
            $this->nameQualifier = FALSE;
        }

        if (isset($config['SPNameQualifier'])) {
            $this->spNameQualifier = $config['SPNameQualifier'];
        } else {
            $this->spNameQualifier = TRUE;
        }

        $this->format = Null;
        if ( ! empty($config[self::FORMAT_KEY])) {
            $this->format = (string) $config[self::FORMAT_KEY];
        }
    }

    /**
     * @param $value array|string The NameID string or array (with a Value entry)
     * @param $IDPNamespace string
     * @return array|string
     * @throws SimpleSAML_Error_Exception
     */
    public function appendIdp($value, $IDPNamespace) {

        $suffix = self::DELIMITER . $IDPNamespace;

        if (is_string($value)) {
            $value .= $suffix;
            return $value;
        }

        if (!isset($value[self::VALUE_KEY])) {
            throw new SimpleSAML_Error_Exception(self::ERROR_PREFIX . "Missing '" .
                self::VALUE_KEY . "' key in NameID entry  for  " .
                $IDPNamespace . "."
            );
        }

        $value[self::VALUE_KEY] = $value[self::VALUE_KEY] . $suffix;
        return $value;
    }


    /**
     * Apply filter to copy attributes.
     *
     * @param array &$state  The current state array
     */
    public function process(&$state) {
        assert('is_array($state)');

        $samlIDP = $state[self::IDP_KEY];

        if (empty($state[self::SP_NAMEID_ATTR])) {
            SimpleSAML_Logger::warning(
                self::SP_NAMEID_ATTR . ' attribute not available from ' .
                $samlIDP . '.'
            );
            return;
        }

        // Get the potential IDPs from idp remote metadata
        $metadataPath = __DIR__ . '/../../../../../metadata';

        // If a unit test sends a different metadataPath, use it
        if (isset($state['metadataPath'])) {
            $metadataPath = $state['metadataPath'];
        }
        $idpEntries = \Sil\SspUtils\Metadata::getIdpMetadataEntries($metadataPath);

        $idpEntry = $idpEntries[$samlIDP];

        // The IDP metadata must have an IDPNamespace entry
        if ( ! isset($idpEntry[self::IDP_CODE_KEY])) {
            throw new SimpleSAML_Error_Exception(self::ERROR_PREFIX . "Missing required metadata entry: " .
                                                 self::IDP_CODE_KEY . ".");
        }

        // IDPNamespace must be a non-empty string
        if ( ! is_string($idpEntry[self::IDP_CODE_KEY])) {
            throw new SimpleSAML_Error_Exception(self::ERROR_PREFIX . "Required metadata " .
                "entry, " . self::IDP_CODE_KEY . ", must be a non-empty string.");
        }

        // IDPNamespace must not have special characters in it
        if ( ! preg_match("/^[A-Za-z0-9_-]+$/", $idpEntry[self::IDP_CODE_KEY])) {
            throw new SimpleSAML_Error_Exception(self::ERROR_PREFIX . "Required metadata " .
                "entry, " . self::IDP_CODE_KEY . ", must not be empty or contain anything except " .
                "letters, numbers, hyphens and underscores.");
        }

        $IDPNamespace = $idpEntry[self::IDP_CODE_KEY];

        $nameId = $state[self::SP_NAMEID_ATTR];
        $nameId = self::appendIdp($nameId, $IDPNamespace);

        $format =  'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';

        if ( ! empty($this->format)) {
            $format = $this->format;
        } elseif ( ! empty($nameId[self::FORMAT_KEY])) {
            $format = $nameId[self::FORMAT_KEY];
        }

        $state['saml:NameID'][$format] = $nameId;

    }

}

?>
