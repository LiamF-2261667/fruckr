<?php

namespace App\DataObjects;

class FoodItemData
{
    /* Attributes */
    public ?string $name = null;
    public ?string $description = null;
    public ?float $price = null;
    public ?array $ingredients = null;
    public ?BlobData $image = null;
    public array $media = [];

    /* Methods */
    /**
     * Parse json data to a food item data object
     * @param object $jsonData The json data
     * @throws \Exception When the data could not be parsed
     */
    public static function parse(object $jsonData): FoodItemData
    {
        try {
            $foodItemData = new FoodItemData();

            $foodItemData->name = $jsonData->name;
            $foodItemData->description = $jsonData->description;
            $foodItemData->price = $jsonData->price;
            $foodItemData->ingredients = $jsonData->ingredients;
            $foodItemData->image = new BlobData($jsonData->image, BlobData::$IMG, true);

            foreach ($jsonData->media as $media)
                $foodItemData->media[] = new BlobData($media->src, $media->type, true);

            return $foodItemData;
        }
        catch (\Exception $e) {
            throw new \Exception("Could not parse food item data: " . $e->getMessage());
        }
    }
}