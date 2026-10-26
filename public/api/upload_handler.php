<?php
/**
 * Route Delegator: Ingestion Upload API Endpoint
 * Delegated to UploadController
 */

require_once __DIR__ . '/../../app/bootstrap.php';

(new UploadController())->handleUploadApi();
