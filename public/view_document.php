<?php
/**
 * Route Delegator: View & Decrypt Document Action
 * Delegated to DocumentController
 */

require_once __DIR__ . '/../app/bootstrap.php';

(new DocumentController())->view();
