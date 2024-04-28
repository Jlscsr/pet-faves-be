<?php


use Helpers\ResponseHelper;
use Helpers\HeaderHelper;

use Models\PetsModel;

class PetsController
{
    private $petsModel;

    /**
     * Constructor for the class.
     *
     * @param PDO $pdo The PDO object for database connection.
     * @return void
     */
    public function __construct($pdo)
    {
        $this->petsModel = new PetsModel($pdo);

        HeaderHelper::setResponseHeaders();
    }

    public function getAllPets()
    {
        ResponseHelper::sendSuccessResponse([], "SIedyhfgauywerfhaer");
    }

    public function getPetByID($param)
    {
    }


    public function addNewPet($payload)
    {
        try {
            if (empty($payload)) {
                ResponseHelper::sendErrorResponse("Invalid payload or payload is empty", 400);
                return;
            }


            $pet = $this->petsModel->addNewPet($payload);

            if (!$pet) {
                ResponseHelper::sendErrorResponse("Failed to add pet", 400);
                return;
            }

            ResponseHelper::sendSuccessResponse($pet, "Pet added successfully");
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
