<?php

/**
 * Custom IdP discovery service.
 */

$discoHandler = new sspmod_sildisco_IdPDisco(['saml20-idp-remote'], 'saml');

$discoHandler->handleRequest();
