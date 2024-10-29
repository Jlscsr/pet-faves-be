<?php

use Helpers\ResponseHelper;

use Validators\HTTPRequestValidator;

use Models\PostsInteractionModel;

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

            $response = $this->postsInteractionModel->addNewPostInteraction($payload);

            if (!$response) {
                return ResponseHelper::sendSuccessResponse([], "Failed to add new like to the post", 404);
            }

            return ResponseHelper::sendSuccessResponse([], 'Successfully added new like to the post');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function deletePostInteraction($params)
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