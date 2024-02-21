<?php

namespace App\Models;

use App\Database\DatabaseHandler;
use App\DataObjects\BlobData;
use App\Exceptions\InvalidInputException;
use App\Exceptions\NoDataException;
use App\Helpers\BlobConverter;
use Cassandra\Blob;

class FoodtruckProfileModel
{
    /* Attributes */
    private ?int $foodtruckId = null;
    private ?string $extraInfo = null;
    private ?string $description = null;
    private ?BlobData $profileImage = null;
    private ?array $bannerImages = null;

    /* Initializers */
    /**
     * Create a new FoodtruckProfileModel
     * @param int $foodtruckId the id of the foodtruck
     * @return FoodtruckProfileModel the created FoodtruckProfileModel
     * @throws NoDataException if no profile is found
     */
    public static function getProfileByFoodtruckKey(int $foodtruckId): FoodtruckProfileModel
    {
        $profile = new FoodtruckProfileModel();
        $profile->loadByFoodtruckId($foodtruckId);
        return $profile;
    }

    /**
     * Create a new FoodtruckProfileModel
     * @param int $foodtruckId the id of the foodtruck
     * @throws NoDataException if no profile is found
     */
    public function loadByFoodtruckId(int $foodtruckId): void
    {
        // Getting the profile data from the db
        $result = DatabaseHandler::getInstance()->query(self::$Q_GET_PROFILE_BY_FOODTRUCK_ID, [$foodtruckId]);
        if (count($result) == 0)
            throw new NoDataException("FoodtruckProfile", "No profile found for foodtruck with id " . $foodtruckId);

        // Loading the profile data inside the model
        $this->foodtruckId = $foodtruckId;
        $this->extraInfo = $result[0]->extraInfo;
        $this->description = $result[0]->description;
        $this->profileImage = new BlobData($result[0]->profileImg, BlobData::$IMG);

        // Getting the banner images
        $this->loadBannerImages();
    }

    /* Methods */
    /**
     * Load the banner images from the database
     */
    private function loadBannerImages(): void
    {
        // Getting the banner images
        $result = DatabaseHandler::getInstance()->query(self::$Q_GET_PROFILE_BANNER_IMAGES, [$this->foodtruckId]);
        if (count($result) == 0)
            $this->bannerImages = [];

        else {
            // Loading the banner images
            $rawBanners = [];
            foreach ($result as $bannerImage)
                $rawBanners[] = array($bannerImage->showOrder, new BlobData($bannerImage->image, $bannerImage->type));

            // Sorting the banner images
            usort($rawBanners, function ($a, $b) {
                return $a[0] <=> $b[0];
            });

            // Loading the banners in order into the model
            $this->bannerImages = [];
            foreach ($rawBanners as $bannerImage)
                $this->bannerImages[] = $bannerImage[1];
        }
    }

    /**
     * Add a banner image
     * @param BlobData $bannerImage the banner image
     */
    public function addBannerImage(BlobData $bannerImage, int $showOrder = 0): void
    {
        $this->bannerImages[] = $bannerImage;
        DatabaseHandler::getInstance()->query(self::$Q_ADD_PROFILE_BANNER_IMG, [$this->foodtruckId, $bannerImage->getBlobData(), $bannerImage->getTypeStr(), $showOrder], false);
    }

    /**
     * Create a new FoodtruckProfileModel
     * @param int $foodtruckId
     * @param string|null $extraInfo the extra info
     * @param string $description the description
     * @param string $profileImage the profile image as a base64 string
     * @param array|null $bannerImages the banner images as BannerDataModels
     * @return FoodtruckProfileModel the created FoodtruckProfileModel
     * @throws InvalidInputException if the inputs are invalid
     */
    public static function createNewProfile(int $foodtruckId, ?string $extraInfo, string $description, string $profileImage, ?array $bannerImages): FoodtruckProfileModel
    {
        // Loading the info inside the model
        $profile = new FoodtruckProfileModel();
        $profile->foodtruckId = $foodtruckId;
        $profile->extraInfo = $extraInfo;
        $profile->description = $description;
        $profile->profileImage = new BlobData($profileImage, BlobData::$IMG, true);
        $profile->bannerImages = $bannerImages;

        // Formatting and validating the inputs
        $profile->format();
        $profile->validate();

        // Storing the profile inside the database
        DatabaseHandler::getInstance()->query(self::$Q_CREATE_PROFILE, [$profile->foodtruckId, $profile->profileImage->getBlobData(), $profile->description, $profile->extraInfo], false);

        // Adding the banner images
        foreach ($bannerImages as $bannerImage)
            $profile->addBannerImage(new BlobData($bannerImage->base64, $bannerImage->type, true), $bannerImage->order);

        return $profile;
    }

    /**
     * Save the profile
     * @param string $profileImageBase64 the profile image as a base64 string
     * @param array $banners the banner images as BannerDataModels
     * @param string $description the description
     * @param string|null $extra the extra info
     * @throws InvalidInputException if the inputs are invalid
     */
    public function save(string $profileImageBase64, array $banners, string $description, string $extra): void
    {
        // Store the old banner images for comparing later on
        $oldBannerImages = $this->bannerImages;

        // Loading the info inside the model
        $this->profileImage = new BlobData($profileImageBase64, BlobData::$IMG, true);
        $this->description = $description;
        $this->extraInfo = $extra;

        // Loading the banner images
        $this->bannerImages = [];
        foreach ($banners as $bannerImage)
            $this->bannerImages[] = new BlobData($bannerImage->base64, $bannerImage->type, true);

        // Formatting and validating the inputs
        $this->format();
        $this->validate();

        // Storing the profile inside the database

        DatabaseHandler::getInstance()->query(self::$Q_UPDATE_PROFILE, [$this->profileImage->getBlobData(), $this->description, $this->extraInfo, $this->foodtruckId], false);

        // Replacing the banner images if necessary
        if ($oldBannerImages != $banners) {
            // Deleting the old banner images
            DatabaseHandler::getInstance()->query(self::$Q_DELETE_PROFILE_BANNER_IMAGES, [$this->foodtruckId], false);

            // Adding the new banner images
            foreach ($banners as $bannerImage)
                $this->addBannerImage(new BlobData($bannerImage->base64, $bannerImage->type, true), $bannerImage->order);
        }
    }

    /**
     * Format the model in a defaulted way
     */
    public function format() {
        // Trimming each string
        if ($this->extraInfo != null) $this->extraInfo = trim($this->extraInfo);
        if ($this->description != null) $this->description = trim($this->description);

        // Replace empty string with null
        if ($this->extraInfo == "") $this->extraInfo = null;
        if ($this->description == "") $this->description = null;
    }

    /**
     * Check if the model contains valid data (like expected from the db)
     * @throws InvalidInputException if the model contains invalid data
     */
    public function validate() {
        // A foodtruck has to be linked to the profile
        if ($this->foodtruckId == null)
            throw new InvalidInputException("foodtruckId", "Foodtruck id is not set");

        // A description has to be set
        if ($this->description == null)
            throw new InvalidInputException("description", "Description: Description is not set");

        // A profile image has to be set
        if ($this->profileImage == null)
            throw new InvalidInputException("profileImage", "Profile image: Profile image is not set");

        // All banner images have to be set
        foreach ($this->bannerImages as $bannerImage)
            if ($bannerImage == null)
                throw new InvalidInputException("bannerImages", "Banners: A banner image is not set correctly");
    }

    /* Queries */
    private static string $Q_GET_PROFILE_BY_FOODTRUCK_ID = "SELECT * FROM FoodtruckProfile WHERE foodtruck = ?";
    private static string $Q_GET_PROFILE_BANNER_IMAGES = "SELECT * FROM FoodtruckBannerImg WHERE foodtruck = ?";

    private static string $Q_CREATE_PROFILE = "INSERT INTO FoodtruckProfile (foodtruck, profileImg, description, extraInfo) VALUES (?, ?, ?, ?)";
    private static string $Q_ADD_PROFILE_BANNER_IMG = "INSERT INTO FoodtruckBannerImg (foodtruck, image, type, showOrder) VALUES (?, ?, ?, ?)";
    private static string $Q_UPDATE_PROFILE = "UPDATE FoodtruckProfile SET profileImg = ?, description = ?, extraInfo = ? WHERE foodtruck = ?";

    private static string $Q_DELETE_PROFILE_BANNER_IMAGES = "DELETE FROM FoodtruckBannerImg WHERE foodtruck = ?";

    /* Getters */
    public function getExtraInfo(): ?string
    {
        return $this->extraInfo;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getProfileImage(): ?BlobData
    {
        return $this->profileImage;
    }

    public function getBannerImages(): ?array
    {
        return $this->bannerImages;
    }
}