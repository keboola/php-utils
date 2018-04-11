<?php

namespace Keboola\Utils;

/**
 * https://github.com/nette/nette/blob/master/Nette/Utils/Strings.php#L188
 * Converts to web safe characters [a-z0-9-] text.
 * @param  string  UTF-8 encoding
 * @param  string  allowed characters
 * @param  bool
 * @return string
 */
function sanitizeColumnName($s, $charlist = '_', $lower = false)
{
    $systemColumns = [
        'oid',
        'tableoid',
        'xmin',
        'cmin',
        'xmax',
        'cmax',
        'ctid',
    ];

    $s = toAscii($s);
    if ($lower) {
        $s = strtolower($s);
    }
    $s = preg_replace('#[^a-z0-9' . preg_quote($charlist, '#') . ']+#i', '_', $s);
    $s = trim($s, '_');
    return in_array(strtolower($s), array_map('strtolower', $systemColumns)) ? "{$s}_" : $s;
}
