<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown by App\Services\InventoryLedgerService when a movement would
 * break the ledger equation (Blueprint 8.3) - e.g. would take balance_after
 * negative outside an authorized adjustment. Same convention as
 * WorkflowTransitionException (a RuntimeException, no custom rendering
 * registered yet in bootstrap/app.php - controllers that call the ledger
 * service catch this explicitly and turn it into a redirect/validation
 * error instead of letting it bubble to the default error page).
 */
class InventoryLedgerException extends RuntimeException
{
}
