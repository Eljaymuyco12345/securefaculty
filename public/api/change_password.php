<?php
/**
 * Route Delegator: Authenticated Change Password API Endpoint
 * Delegated to AuthController
 */

require_once __DIR__ . '/../../app/bootstrap.php';

(new AuthController())->handleChangePasswordApi();
