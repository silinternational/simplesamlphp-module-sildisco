<?php
/**
 *
 * Note: This has been copied from the core code (www/saml2/idp/SSOService.php)
 *   and modified to call a different authentication class/method
 *
 * Original comments ...
 *
 * The SSOService is part of the SAML 2.0 IdP code, and it receives incoming Authentication Requests
 * from a SAML 2.0 SP, parses, and process it, and then authenticates the user and sends the user back
 * to the SP with an Authentication Response.
 *
 * @author Andreas �kre Solberg, UNINETT AS. <andreas.solberg@uninett.no>
 * @package SimpleSAMLphp
 */

require_once('../../_include.php');

SimpleSAML_Logger::info('SAML2.0 - IdP.SSOService: Accessing SAML 2.0 IdP endpoint SSOService');

$metadata = SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
$idpEntityId = $metadata->getMetaDataCurrentEntityID('saml20-idp-hosted');
$idp = SimpleSAML_IdP::getById('saml2:' . $idpEntityId);

$config = SimpleSAML_Configuration::getConfig();
$hubModeKey = 'hubmode';

try {
// If in hub mode, then use the sildisco entry script
    if ($config->getValue($hubModeKey, False)) {
        sspmod_sildisco_IdP_SAML2::receiveAuthnRequest($idp);
    } else {
        sspmod_saml_IdP_SAML2::receiveAuthnRequest($idp);        
    }
} catch (Exception $e) {
    if ($e->getMessage() === "Unable to find the current binding.") {
        throw new SimpleSAML_Error_Error('SSOPARAMS', $e, 400);
    } else {
        throw $e; // do not ignore other exceptions!
    }
}

assert('FALSE');
