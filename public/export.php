<?php
/**
 * Route Delegator: Export Action
 * Delegated to ExportController
 */

require_once __DIR__ . '/../app/bootstrap.php';

(new ExportController())->index();
