<?php
/**
 * Route Delegator: Plan B Database Backup Download
 * Delegated to DashboardController
 */
require_once __DIR__ . '/../app/bootstrap.php';

(new DashboardController())->backup();
