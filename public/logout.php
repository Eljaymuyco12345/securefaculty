<?php
/**
 * Route Delegator: Logout Action
 * Delegated to AuthController
 */

require_once __DIR__ . '/../app/bootstrap.php';

(new AuthController())->logout();
