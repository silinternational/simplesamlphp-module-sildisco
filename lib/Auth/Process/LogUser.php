<?php

use Sil\SspUtils\Metadata;

/**
 *
 */
class sspmod_sildisco_Auth_Process_LogUser extends SimpleSAML_Auth_ProcessingFilter
{
    const IDP_KEY = "saml:sp:IdP"; // the key that points to the entity id in the state

    // the metadata key for the IDP's Namespace code (i.e. short name)
    const IDP_CODE_KEY = 'IDPNamespace';


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
        $datetime = date("Y-m-d.H:i:s");
    }

}