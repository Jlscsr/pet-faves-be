<?php

namespace App\Controllers;

use Ramsey\Uuid\Uuid;

use App\Helpers\ResponseHelper;

use App\Validators\HTTPRequestValidator;

use App\Models\PetCareModel;

use RuntimeException;

class PetCareController
{
    private $pdo;
    private $petCareModel;
    private $acceptableParamsKeys = ['id', 'status'];
    private $expectedPostPayloadKeys = ['title', 'category', 'featuredImageURL', 'description', 'content', 'status'];
    private $acceptablePayloadKeys = ['title', 'category', 'featuredImageURL', 'description', 'content'];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->petCareModel = new PetCareModel($this->pdo);
    }

    public function getAllPetCarePosts()
    {
        try {
            $petCatePosts = $this->petCareModel->getAllPetCarePosts();

            if (!$petCatePosts) {
                return ResponseHelper::sendSuccessResponse([], 'No posts found');
            }

            return ResponseHelper::sendSuccessResponse($petCatePosts, 'Posts found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAllPetCarePostsByStatus(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $status = $params['status'];

            $petCarePosts = $this->petCareModel->getAllPetCarePostsByStatus($status);

            if (!$petCarePosts) {
                return ResponseHelper::sendSuccessResponse([], 'No posts found');
            }

            return ResponseHelper::sendSuccessResponse($petCarePosts, 'Posts found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function addNewPetCarePost(array $payload)
    {
        try {
            HTTPRequestValidator::validatePOSTPayload($this->expectedPostPayloadKeys, $payload);

            $uuid = Uuid::uuid7()->toString();
            $payload['id'] = $uuid;

            $response = $this->petCareModel->addNewPetCarePost($payload);


            if (!$response) {
                return ResponseHelper::sendErrorResponse("Failed to add post", 400);
            }

            return ResponseHelper::sendSuccessResponse($response, "Post added successfully");
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function updatePetCarePost(array $params, array $payload)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);
            HTTPRequestValidator::validateGETParameter($this->expectedPostPayloadKeys, $payload);

            $id = $params['id'];

            $response = $this->petCareModel->updatePetCarePost($id, $payload);

            if (!$response) {
                return ResponseHelper::sendErrorResponse("Failed to update post", 400);
            }

            return ResponseHelper::sendSuccessResponse([], "Post updated successfully");
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function deletePetCarePost(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $id = $params['id'];

            $response = $this->petCareModel->deletePetCarePost($id);

            if (!$response) {
                return ResponseHelper::sendErrorResponse("Failed to delete post", 400);
            }

            return ResponseHelper::sendSuccessResponse([], "Post deleted successfully");
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
