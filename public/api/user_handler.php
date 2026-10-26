<?php
/**
 * Route Delegator: User Management API Endpoint
 * Delegated to UserController
 */

require_once __DIR__ . '/../../app/bootstrap.php';

(new UserController())->handleApi();
