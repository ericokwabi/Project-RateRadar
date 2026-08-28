<?php

namespace App\Services;

use RuntimeException;

/**
 * Lightspeed antwoordde met HTTP 429. Apart van de andere fouten, omdat dit
 * juist de gebeurtenis is die RateRadar wil vastleggen in plaats van wegslikken.
 */
class TooManyRequestsException extends RuntimeException
{
}
