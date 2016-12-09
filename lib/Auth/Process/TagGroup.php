<?php


use Sil\SspUtils\Metadata;

/**
 * Attribute filter for prefixing group names
 *
 */
class sspmod_sildisco_Auth_Process_TagGroup extends SimpleSAML_Auth_ProcessingFilter {

    const IDP_NAME_KEY = 'name'; // the metadata key for the IDP's name

    const IDP_CODE_KEY = 'IDPCode'; // the metadata key for the IDP's code (i.e. short name)

    /**
     * Apply filter to copy attributes.
     *
     * @param array &$request  The current request
     */
    public function process(&$request) {
        assert('is_array($request)');
        assert('array_key_exists("Attributes", $request)');

        $attributes =& $request['Attributes'];

        // urn:oid:2.5.4.31  is for 'member' (like groups)
        $oid4member = 'urn:oid:2.5.4.31';

        if (! isset($attributes[$oid4member])) {
            return;
        }

        // Get the potential IDPs from idp remote metadata
        $metadataPath = __DIR__ . '/../../../../../metadata';
        $idpEntries = \Sil\SspUtils\Metadata::getIdpMetadataEntries($metadataPath);
        
        $newGroups = array();
        $samlIDP = $request["saml:sp:IdP"];

        $idpEntry = $idpEntries[$samlIDP];

        /*
         *  If the IDP metadata has an IDPCode entry, use that value.  Otherwise,
         * if there is a name entry, use that value.  Otherwise,
         * use the IDP's entity id.
         */
        if (isset($idpEntry[self::IDP_CODE_KEY])) {
            $idp = $idpEntry[self::IDP_CODE_KEY];
        } else if (isset($idpEntry[self::IDP_NAME_KEY])) {
            $idp = $idpEntry[self::IDP_NAME_KEY];
        } else {
            $idp = $samlIDP;
        }

        $idp = str_replace(' ', '_', $idp);
        $delimiter = '|';

        foreach($attributes[$oid4member] as $group) {
            $newGroups[] = "idp$delimiter$idp$delimiter$group";
        }

        $attributes[$oid4member] = $newGroups;
    }

}

?>
