<?php

use Sil\SspUtils\AnnouncementUtils;
use Sil\SspUtils\DiscoUtils;

/**
 * This class implements a custom IdP discovery service, for use with a ssp hub (proxy)
 *
 * This module extends the basic IdP disco handler.
 *
 * @author Steve Bagwell SIL GTIS
 * @package SimpleSAMLphp
 */
class sspmod_sildisco_IdPDisco extends SimpleSAML_XHTML_IdPDisco
{

    /**
     * Log a message.
     *
     * This is an helper function for logging messages. It will prefix the messages with our discovery service type.
     *
     * @param string $message The message which should be logged.
     */
    protected function log($message)
    {
        SimpleSAML_Logger::info('SildiscoIdPDisco.'.$this->instance.': '.$message);
    }

    /**
     * Handles a request to this discovery service.
     *
     * The IdP disco parameters should be set before calling this function.
     */
    public function handleRequest()
    {
        $this->start();

        // no choice made. Show discovery service page
        $idpList = $this->getIdPList();
        $idpList = $this->filterList($idpList);

        $metadataPath = __DIR__ . '/../../../metadata/';

        $sessionDataType = 'sildisco:authentication';
        $sessionKey = 'spentityid';
        $spEntityId = $this->session->getData($sessionDataType, $sessionKey);

        $idpList = DiscoUtils::getReducedIdpList(
            $idpList,
            $metadataPath,
            $spEntityId
        );

        if (sizeof($idpList) == 1) {
            $this->log(
                'Choice made [' . array_keys($idpList)[0] . '] (Redirecting the user back. returnIDParam='.
                $this->returnIdParam.')'
            );

            \SimpleSAML\Utils\HTTP::redirectTrustedURL(
                $this->returnURL,
                array($this->returnIdParam => array_keys($idpList)[0])
            );
        }
        
        /* Tag if user is a beta tester */
        $sessionType = 'sildisco:authentication';
        $sessionKey = 'beta_tester';

        $session = SimpleSAML_Session::getSessionFromRequest();
        $betaTesterValue = $session->getData($sessionType, $sessionKey);

        $templateFileName = 'selectidp-' . $this->config->getString('idpdisco.layout', 'links') . '.php';

        $t = new SimpleSAML_XHTML_Template($this->config, $templateFileName, 'disco');
        $t->data['idplist'] = $idpList;
        $t->data['return'] = $this->returnURL;
        $t->data['returnIDParam'] = $this->returnIdParam;
        $t->data['entityID'] = $this->spEntityId;
        $t->data['urlpattern'] = htmlspecialchars(\SimpleSAML\Utils\HTTP::getSelfURLNoQuery());
        $t->data['announcement'] = AnnouncementUtils::getSimpleAnnouncement();
        $t->data['betatester'] = $betaTesterValue; //  This will be 1 or null.

        $t->show();
    }

}
