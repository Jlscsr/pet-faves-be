<?php

use Models\PostsInteractionModel;

use Helpers\ResponseHelper;
use Helpers\HeaderHelper;


class PostsInteractionController
{
    private $pdo;
    private $postsModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->postsInteractionModel = new PostsInteractionModel($this->pdo);

        HeaderHelper::setResponseHeaders();
    }

    public function getAllPostLikesByPostID($posts)
    {
        try {
            if (!is_array($posts)) {
                ResponseHelper::sendErrorResponse("Invalid or missing posts parameter", 400);
                return;
            }

            $response = $this->postsInteractionModel->getAllPostLikesByPostID($posts);

            if (!$response) {
                ResponseHelper::sendSuccessResponse([], "No posts found", 404);
                exit;
            }

            ResponseHelper::sendSuccessResponse($response, 'Successfully fetched posts');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function addNewPostInteraction($payload)
    {
        try {
            if (!is_array($payload) && empty($payload)) {
                ResponseHelper::sendErrorResponse("Invalid or missing payload parameter", 400);
                return;
            }

            $response = $this->postsInteractionModel->addNewPostInteraction($payload);

            if (!$response) {
                ResponseHelper::sendSuccessResponse([], "Failed to add new like to the post", 404);
                exit;
            }

            ResponseHelper::sendSuccessResponse([], 'Successfully added new like to the post');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function deletePostInteraction($params)
    {
        try {
            if (!is_array($params) && empty($params)) {
                ResponseHelper::sendErrorResponse("Invalid or missing payload parameter", 400);
                return;
            }

            $response = $this->postsInteractionModel->deletePostInteraction($params);

            if (!$response) {
                ResponseHelper::sendErrorResponse("Failed to delete like to the post");
                exit;
            }

            ResponseHelper::sendSuccessResponse([], 'Successfully deleted like to the post');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
