<?php

namespace App\DataObjects;

use App\Helpers\BlobConverter;

/**
 * Object to store blob data from the database
 */
class BlobData
{
    /* Attributes */
    private string $base64Data;
    private int $type;

    public static int $IMG = 0;
    public static int $VID = 1;

    /**
     * Create a new BlobData
     * @param $rawInput array|string raw input from the database
     * @param int|string $type the type of the blob data
     * @param bool $base64 whether the raw input is already base64 encoded
     */
    public function __construct($rawInput, $type, bool $base64 = false)
    {
        if (!$base64)
        	$this->base64Data = BlobConverter::blobArrayToBase64($rawInput);
        else
            $this->base64Data = $rawInput;

        if (is_string($type))
            $this->type = $this->strTypeToInt($type);
        else
            $this->type = $type;
    }

    /* Methods */
    /**
     * Convert the blob data to html
     * @param string $extraHtmlTags extra html tags to add to the html element
     * @return string the html element
     */
    public function toHtml(string $extraHtmlTags = ""): string
    {
        if ($this->type === self::$IMG)
            return '<img ' . $extraHtmlTags . ' src="data:image/jpg;base64,' . $this->base64Data . '">';

        else if ($this->type === self::$VID) {
            return '<video ' . $extraHtmlTags . '>
                        <source src="data:video/mp4;base64,' . $this->base64Data . '" type="video/mp4">
                    </video>';
        }

        else
            return 'Unknown type';
    }

    private function strTypeToInt(string $type) : int
    {
        if (strtoupper($type) === "IMG")
            return self::$IMG;
        else if (strtoupper($type) === "VID")
            return self::$VID;
        else
            return -1;
    }

    /* Getters*/
    public function getBase64Data(): string
    {
        return $this->base64Data;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getTypeStr(): string
    {
        return $this->type === self::$VID ? "VID" : "IMG";
    }

    public function getBlobData(): string
    {
        return BlobConverter::base64ToBlobString($this->base64Data);
    }
}