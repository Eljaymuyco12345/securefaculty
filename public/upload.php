<?php
/**
 * Route Delegator: Ingestion Gateway Action
 * Delegated to UploadController
 */

require_once __DIR__ . '/../app/bootstrap.php';

(new UploadController())->index();
