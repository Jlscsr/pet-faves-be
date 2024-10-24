<?php

use Models\PostsModel;

use Helpers\ResponseHelper;
use Helpers\HeaderHelper;

use Validators\Controllers\PetFeedsValidator;

class PostsController
{
    private $pdo;
    private $postsModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->postsModel = new PostsModel($this->pdo);

        HeaderHelper::setResponseHeaders();
    }

    public function getAllPosts($param)
    {
        try {
            if (empty($param)) {
                ResponseHelper::sendErrorResponse("Invalid or missing payload parameter", 400);
                return;
            }
            $limit = (int) $_GET['limit'] ?? 0;
            $offset = (int) $_GET['offset'] ?? 0;

            $response = $this->postsModel->getAllPosts($param['approvalStatus'], $offset, $limit);

            if (!$response) {
                ResponseHelper::sendSuccessResponse([], "No posts found.");
                exit;
            }

            ResponseHelper::sendSuccessResponse($response, 'Successfully fetched posts.');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAllPostsByTypeOfPost($param)
    {
        try {

            $limit = (int) $_GET['limit'] ?? 0;
            $offset = (int) $_GET['offset'] ?? 0;

            if (empty($param) && !isset($param['typeOfPost'])) {
                ResponseHelper::sendErrorResponse("Invalid or missing typeOfPost parameter", 400);
                return;
            }

            $typeOfPost = $param['typeOfPost'] ?? '';

            $response = $this->postsModel->getAllPostsByTypeOfPost($typeOfPost, $offset, $limit);

            if (!$response) {
                ResponseHelper::sendSuccessResponse([], "No posts found.");
                exit;
            }

            ResponseHelper::sendSuccessResponse($response, 'Successfully fetched posts.');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function addNewPost($payload)
    {
        try {
            if (empty($payload)) {
                ResponseHelper::sendErrorResponse("Invalid or missing payload parameter", 400);
                return;
            }

            $response = $this->postsModel->addNewPost($payload);

            if (!$response) {
                ResponseHelper::sendSuccessResponse([], "Post successfully added.");
                exit;
            }

            ResponseHelper::sendSuccessResponse([], 'Successfully added post.');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function addNewPostMedia($payload)
    {
        try {
            if (empty($payload)) {
                ResponseHelper::sendErrorResponse("Invalid or missing payload parameter", 400);
                return;
            }

            $response = $this->postsModel->addNewPostMedia($payload);

            if (!$response) {
                ResponseHelper::sendSuccessResponse([], "Post media successfully added.");
                exit;
            }

            ResponseHelper::sendSuccessResponse($response, 'Successfully added post media.');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function addNewEventPost($payload)
    {
        try {
            if (empty($payload)) {
                ResponseHelper::sendErrorResponse("Invalid or missing payload parameter", 400);
                return;
            }

            $response = $this->postsModel->addNewEventPost($payload);

            if (!$response) {
                ResponseHelper::sendSuccessResponse([], "Post event successfully added.");
                exit;
            }

            ResponseHelper::sendSuccessResponse([], 'Successfully added post event.');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function updatePostMedia($param, $payload)
    {
        try {
            if (empty($param) && !isset($param['postID'])) {
                ResponseHelper::sendErrorResponse("Invalid or missing postID parameter", 400);
                return;
            }

            $response = $this->postsModel->updatePostMedia($param['postID'], $payload['mediaURL']);

            if (!$response) {
                ResponseHelper::sendErrorResponse("Failed to upload media file", 400);
                exit;
            }

            ResponseHelper::sendSuccessResponse([], 'Successfully uploaded media file.');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function updatePostApprovalStatus($param, $payload)
    {
        try {
            if (empty($param) && !isset($param['postID'])) {
                ResponseHelper::sendErrorResponse("Invalid or missing postID parameter", 400);
                return;
            }

            $response = $this->postsModel->updatePostApprovalStatus($param['postID'], $payload['approvalStatus'], $param['postType']);

            if (!$response) {
                ResponseHelper::sendErrorResponse("Failed to update post approval status", 400);
                exit;
            }

            ResponseHelper::sendSuccessResponse([], 'Successfully updated post approval status.');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
