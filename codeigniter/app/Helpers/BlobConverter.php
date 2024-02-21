<?php

namespace App\Helpers;

class BlobConverter
{
    public static function blobArrayToString($blob): string
    {
        if (gettype($blob) == "string")
            return $blob;

        $string = "";

        foreach ($blob as $byte) {
            $string .= chr($byte);
        }

        return $string;
    }

    public static function blobArrayToBase64($blob): string
    {
        return base64_encode(self::blobArrayToString($blob));
    }

    public static function base64ToBlobString($base64): string
    {
        return base64_decode($base64);
    }
}