<?php


use Sil\SspUtils\Metadata;

/**
 * Attribute filter for prefixing group names
 *
 */
class sspmod_sildisco_Auth_Process_AddIdp2NameId extends SimpleSAML_Auth_ProcessingFilter {

    const IDP_KEY = "saml:sp:IdP"; // the key that points to the entity id in the state

    const IDP_CODE_KEY = 'IDPCode'; // the metadata key for the IDP's code (i.e. short name)

    const DELIMITER = '@'; // The symbol between the NameID proper and the Idp code.

    const NAMEID_ATTR = 'saml:NameID'; // The attribute key for the NameID

    const VALUE_KEY = 'Value';  // The value key for the NamedID entry

    const ERROR_PREFIX = "AddIdp2NameId: "; // Text to go at the beginning of error messages


    public function appendIdp(&$entry, $idpCode) {

        if ( ! isset($entry[self::VALUE_KEY])) {
            throw new SimpleSAML_Error_Exception(self::ERROR_PREFIX . "Missing '" .
                self::VALUE_KEY . "' key in NameID entry  for  " .
                self::IDP_CODE_KEY . ".");
        }

        $entry[self::VALUE_KEY] = self::VALUE_KEY . self::DELIMITER . $idpCode;
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


        $samlIDP = $state[self::IDP_KEY];

        if ( ! isset($state['Attributes'][self::NAMEID_ATTR]) ||
            count($state['Attributes'][self::NAMEID_ATTR]) === 0) {
            SimpleSAML_Logger::warning(
                self::NAMEID_ATTR . ' attribute not available from ' .
                $samlIDP . '.'
            );
            return;
        }

        // Get the potential IDPs from idp remote metadata
        $metadataPath = __DIR__ . '/../../../../../metadata';
        $idpEntries = \Sil\SspUtils\Metadata::getIdpMetadataEntries($metadataPath);

        $idpEntry = $idpEntries[$samlIDP];

        if ( ! isset($idpEntry[self::IDP_CODE_KEY])) {
            throw new SimpleSAML_Error_Exception(self::ERROR_PREFIX . "Missing required metadata entry: " .
                                                 self::IDP_CODE_KEY . ".");
        }

        if ( ! is_string($idpEntry[self::IDP_CODE_KEY])) {
            throw new SimpleSAML_Error_Exception(self::ERROR_PREFIX . "Required metadata " .
                "entry, " . self::IDP_CODE_KEY . ", must be a string.");
        }

        $idpCode = $idpEntry[self::IDP_CODE_KEY];

        foreach ($state['Attributes'][self::NAMEID_ATTR] as $nextFormat => $nextEntry) {
            self::appendIdp($nextEntry, $idpCode);
        }

    }

}

?>