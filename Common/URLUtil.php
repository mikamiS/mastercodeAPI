<?php

class URLUtil {
    public static function addQueryParameter($url, $descriptor, $value)
    {
        if ($value !== null)
        {
            return $url."&".$descriptor."=".rawurlencode($value);
        }
        else
        {
            return $url;
        }
    }
} 