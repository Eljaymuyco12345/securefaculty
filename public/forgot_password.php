<?php
/**
 * Route Delegator: Forgot Password Page
 * Delegated to AuthController
 */

require_once __DIR__ . '/../app/bootstrap.php';

(new AuthController())->showForgotPassword();
