<?php
/**
 * Route Delegator: Forgot Password 6-Digit OTP API Endpoint
 * Delegated to AuthController
 */

require_once __DIR__ . '/../../app/bootstrap.php';

(new AuthController())->handleForgotPasswordApi();
