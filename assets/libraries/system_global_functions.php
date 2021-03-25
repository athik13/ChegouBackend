<?php
/**
 * Case-insensitive in_array() wrapper.
 *
 * @param  mixed $needle   Value to seek.
 * @param  array $haystack Array to seek in.
 *
 * @return bool
 */
function in_array_ci($needle, $haystack){
	return in_array(strtolower($needle), array_map('strtolower', $haystack));
}

function startsWithSGF($string, $startString) {
    $len = strlen($startString);
    return (substr($string, 0, $len) === $startString);
}

function endsWithSGF($haystack, $needle) {
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function lengthCountSortSGF($a, $b) {
    return strlen($b) - strlen($a);
}
?>