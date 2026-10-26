<?php
/**
 * Route Delegator: Compliance Audit Ledger Action
 * Delegated to AuditController
 */

require_once __DIR__ . '/../app/bootstrap.php';

(new AuditController())->index();
