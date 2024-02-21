<?php

namespace App\Models;

class FoodtruckDataModel
{
    /* Banner */
    public string $profileImageBase64;
    public string $name;
    public array $banners; /* banners[0].base64 && banners[0].type */

    /* Information */
    public string $email;
    public string $phoneNumber;
    public string $city;
    public string $street;
    public string $postalCode;
    public string $houseNr;
    public ?string $bus;

    /* Tags */
    public array $tags;

    /* Open On */
    public array $openOn; /* openOn[0].day && openOn[0].openTime && openOn[0].closeTime */

    /* Extra */
    public ?string $extra;

    /* Description */
    public string $description;

    /* Future Locations */
    public array $futureLocations; /* futureLocations[0].city && futureLocations[0].street && futureLocations[0].postalCode && futureLocations[0].houseNr && futureLocations[0].bus && futureLocations[0].date && futureLocations[0].startTime && futureLocations[0].endTime */
}

class BannerDataModel
{
    public string $base64;
    public string $type;
    public int $order;
}

class OpenOnDataModel
{
    public string $day;
    public string $openTime;
    public string $closeTime;
}

class FutureLocationDataModel
{
    public string $city;
    public string $street;
    public string $postalCode;
    public string $houseNr;
    public ?string $bus;
    public string $date;
}