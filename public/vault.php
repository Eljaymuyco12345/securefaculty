<?php
/**
 * Route Delegator: Vault Action
 * Delegated to VaultController
 */

require_once __DIR__ . '/../app/bootstrap.php';

(new VaultController())->index();
