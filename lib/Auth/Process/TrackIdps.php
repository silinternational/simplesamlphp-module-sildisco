<?php



/**
 * Attribute filter for adding Idps to the session
 *
 */
class sspmod_ssphub_Auth_Process_TrackIdps extends SimpleSAML_Auth_ProcessingFilter {

    /**
     * Apply filter to save IDPs to session.
     *
     * @param array &$request  The current request
     */
    public function process(&$request) {
        // get the authenticating Idp and add it to the list of previous ones
        $auth = new SimpleSAML_Auth_Simple("ssp-hub");
        $session = SimpleSAML_Session::getSessionFromRequest();
        $sessionDataType = "ssphub:authentication";
        $sessionKey = "authenticated_idps";
    
        $sessionValue = $session->getData($sessionDataType, $sessionKey);
        if ( ! $sessionValue) {
            $sessionValue = [];
        }
    
        // Will we need to wrap the idp in htmlspecialchars()
        $authIdps = $session->getAuthData("hub-discovery", "saml:AuthenticatingAuthority");
        $sessionValue[] = $authIdps[0];
    
        $session->setData($sessionDataType, $sessionKey, $sessionValue); 
    }        
        

}

?>