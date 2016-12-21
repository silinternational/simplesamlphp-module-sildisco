<?php


use Sil\SspUtils\Metadata;

/**
 * Attribute filter for prefixing group names
 *
 */
class sspmod_sildisco_Auth_Process_TagGroup extends SimpleSAML_Auth_ProcessingFilter {

    const IDP_NAME_KEY = 'name'; // the metadata key for the IDP's name

    const IDP_CODE_KEY = 'IDPCode'; // the metadata key for the IDP's code (i.e. short name)

    
    public function prependIdp2Groups($attributes, $attributeLabel, $idpLabel) {
        $newGroups = [];
        $delimiter = '|';

        foreach($attributes[$attributeLabel] as $group) {
            $newGroups[] = "idp" . $delimiter . $idpLabel . $delimiter . $group;
        }
        return $newGroups;
    }
        
    
    /**
     * Apply filter to copy attributes.
     *
     * @param array &$state  The current request
     */
    public function process(&$state) {
        assert('is_array($request)');
        assert('array_key_exists("Attributes", $request)');

        $attributes =& $state['Attributes'];

        // urn:oid:2.5.4.31  is for 'member' (like groups)
        $oid4member = 'urn:oid:2.5.4.31';
        $member = 'member';

        if (empty($attributes[$oid4member]) && empty($attributes[$member])) {
            return;
        }

        // Get the potential IDPs from idp remote metadata
        $metadataPath = __DIR__ . '/../../../../../metadata';

        // If a unit test sends a different metadataPath, use it
        if (isset($state['metadataPath'])) {
            $metadataPath = $state['metadataPath'];
        }

        $idpEntries = \Sil\SspUtils\Metadata::getIdpMetadataEntries($metadataPath);
        
        $samlIDP = $state["saml:sp:IdP"];

        $idpEntry = $idpEntries[$samlIDP];

        /*
         *  If the IDP metadata has an IDPCode entry, use that value.  Otherwise,
         * if there is a name entry, use that value.  Otherwise,
         * use the IDP's entity id.
         */
        if (isset($idpEntry[self::IDP_CODE_KEY]) &&
                is_string($idpEntry[self::IDP_CODE_KEY])) {
            $idpLabel = $idpEntry[self::IDP_CODE_KEY];
        } else if (isset($idpEntry[self::IDP_NAME_KEY]) &&
                is_string($idpEntry[self::IDP_NAME_KEY])) {
            $idpLabel = $idpEntry[self::IDP_NAME_KEY];
        } else {
            $idpLabel = $samlIDP;
        }

        $idpLabel = str_replace(' ', '_', $idpLabel);
        
        foreach ([$oid4member, $member] as $nextAttribute) {
            if ( ! empty($attributes[$nextAttribute])) {
                $attributes[$nextAttribute] = self::prependIdp2Groups(
                    $attributes,
                    $nextAttribute,
                    $idpLabel);
            }
        }
    }

}

?>
