<?php

declare(strict_types=1);

namespace Keboola\Utils;

/**
 * @param mixed $castToBool
 */
function castToBool($castToBool): bool
{
    if (is_string($castToBool)) {
        if ($castToBool === '1') {
            return true;
        }
        if ($castToBool === 'true') {
            return true;
        }
    }
    if (is_int($castToBool) && $castToBool === 1) {
        return true;
    }

    if (is_bool($castToBool) && $castToBool === true) {
        return true;
    }

    return false;
}
