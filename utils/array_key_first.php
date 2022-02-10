<?php
function array_key_first(array $array) {
    foreach ($array as $key => $value) {
        return $key;
    }
    return null;
}

