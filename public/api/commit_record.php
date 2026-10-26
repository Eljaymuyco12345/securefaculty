<?php
/**
 * Route Delegator: Vault Commit & Encryption API Endpoint
 * Delegated to UploadController
 */

require_once __DIR__ . '/../../app/bootstrap.php';

(new UploadController())->handleCommitApi();
