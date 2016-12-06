<?php


use Sil\SspUtils\AuthSourcesUtils;

/**
 * Attribute filter for prefixing group names
 *
 */
class sspmod_sildisco_Auth_Process_TagGroup extends SimpleSAML_Auth_ProcessingFilter {

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

        // Get the potential IDPs from authsources.php
        try {
            $configPath = __DIR__ . '/../../../../../config';
            $authSourcesConfig = AuthSourcesUtils::getAuthSourcesConfig($configPath);
            $authIdps = AuthSourcesUtils::getIdpsFromAuthSources($authSourcesConfig);
            $idpCodes = array_flip($authIdps);
        } catch (\Exception $e) {
            $idpCodes = [];
        }
        
        $newGroups = array();
        $samlIDP = $request["saml:sp:IdP"];

        if (isset($idpCodes[$samlIDP])) {
            $idp = $idpCodes[$samlIDP];
        } else {
            $idp = $samlIDP;
        }

        $delimiter = '|';

        foreach($attributes[$oid4member] as $group) {
            $newGroups[] = "idp$delimiter$idp$delimiter$group";
        }

        $attributes[$oid4member] = $newGroups;
    }

}

?>