<?php
/**
 * Route Delegator: User Management Action
 * Delegated to UserController
 */

require_once __DIR__ . '/../app/bootstrap.php';

(new UserController())->index();
