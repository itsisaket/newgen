<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown by CarbonCalculationService when a CarbonActivity is Approved but
 * has no matching EmissionFactor to calculate against (e.g. its category
 * has never had a factor defined, or its activity_date falls before any
 * factor's effective_from) - same "fail loud, don't silently write a
 * wrong/zero number" role as InventoryLedgerException.
 */
class CarbonCalculationException extends Exception
{
}
