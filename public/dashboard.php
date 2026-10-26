<?php
/**
 * Route Delegator: Dean Dashboard Action
 * Delegated to DashboardController
 */

require_once __DIR__ . '/../app/bootstrap.php';

(new DashboardController())->index();
