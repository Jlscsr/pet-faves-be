<?php

namespace App\Controllers;

use Ramsey\Uuid\Uuid;

use App\Helpers\ResponseHelper;

use App\Validators\HTTPRequestValidator;

use App\Models\PostsInteractionModel;

use RuntimeException;

class PostsInteractionController
{
    private $pdo;
    private $postsInteractionModel;
    private $acceptableKeys = ['postID', 'userID'];
    private $expectedPostPayloadKeys = ['postID', 'userID', 'typeOfPost'];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->postsInteractionModel = new PostsInteractionModel($this->pdo);
    }

    public function addNewPostInteraction(array $payload)
    {
        try {
            HTTPRequestValidator::validatePOSTPayload($this->expectedPostPayloadKeys, $payload);

            $uuid = Uuid::uuid7()->toString();
            $payload['id'] = $uuid;

            $response = $this->postsInteractionModel->addNewPostInteraction($payload);

            if (!$response) {
                return ResponseHelper::sendErrorResponse("Failed to add new like to the post");
            }

            return ResponseHelper::sendSuccessResponse([], 'Successfully added new like to the post');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function deletePostInteraction(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableKeys, $params);

            $postID = $params['postID'];
            $userID = $params['userID'];

            $response = $this->postsInteractionModel->deletePostInteraction($postID, $userID);

            if (!$response) {
                return ResponseHelper::sendErrorResponse("Failed to delete like to the post");
            }

            return ResponseHelper::sendSuccessResponse([], 'Successfully deleted like to the post');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
