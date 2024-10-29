<?php

use Models\PostsModel;

use Helpers\ResponseHelper;

use Validators\HTTPRequestValidator;

class PostsController
{
    private $pdo;
    private $postsModel;
    private $acceptableParamsKeys = ['approvalStatus', 'postID', 'postType', 'typeOfPost'];
    private $commonPostPayloadKeys = ['userID', 'postDescription', 'approvalStatus', 'postType'];
    private $expectedPutMediaPayloadKeys = ['mediaURL'];

    private $expectedPostPayloadKeys;
    private $expectedPostMediaPayloadKeys;
    private $expectedEventPostPayloadKeys;
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->postsModel = new PostsModel($this->pdo);

        $this->expectedPostPayloadKeys = $this->commonPostPayloadKeys;
        $this->expectedPostMediaPayloadKeys = array_merge($this->commonPostPayloadKeys, ['mediaURL', 'mediaType']);
        $this->expectedEventPostPayloadKeys = array_merge($this->commonPostPayloadKeys, [
            'eventDate',
            'eventTime',
            'eventLocation'
        ]);
    }

    public function getAllPosts(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $approvalStatus = $params['approvalStatus'];
            $limit = (int) $_GET['limit'] ?? 0;
            $offset = (int) $_GET['offset'] ?? 0;

            $response = $this->postsModel->getAllPosts($approvalStatus, $offset, $limit);

            if (!$response) {
                return ResponseHelper::sendSuccessResponse([], "No posts found.");
            }

            return ResponseHelper::sendSuccessResponse($response, 'Successfully fetched posts.');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAllPostsByTypeOfPost(array $params)
    {
        try {

            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $typeOfPost = $params['typeOfPost'] ?? '';
            $limit = (int) $_GET['limit'] ?? 0;
            $offset = (int) $_GET['offset'] ?? 0;

            $response = $this->postsModel->getAllPostsByTypeOfPost($typeOfPost, $offset, $limit);

            if (!$response) {
                return ResponseHelper::sendSuccessResponse([], "No posts found.");
            }

            return ResponseHelper::sendSuccessResponse($response, 'Successfully fetched posts.');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function addNewPost($payload)
    {
        try {
            HTTPRequestValidator::validatePOSTPayload($this->expectedPostPayloadKeys, $payload);

            $response = $this->postsModel->addNewPost($payload);

            if (!$response) {
                return ResponseHelper::sendSuccessResponse([], "Post successfully added.");
            }

            return ResponseHelper::sendSuccessResponse([], 'Successfully added post.');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function addNewPostMedia($payload)
    {
        try {
            HTTPRequestValidator::validatePOSTPayload($this->expectedPostMediaPayloadKeys, $payload);

            $response = $this->postsModel->addNewPostMedia($payload);

            if (!$response) {
                return ResponseHelper::sendSuccessResponse([], "Post media successfully added.");
            }

            return ResponseHelper::sendSuccessResponse($response, 'Successfully added post media.');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function addNewEventPost($payload)
    {
        try {
            HTTPRequestValidator::validatePOSTPayload($this->expectedEventPostPayloadKeys, $payload);

            $response = $this->postsModel->addNewEventPost($payload);

            if (!$response) {
                return ResponseHelper::sendSuccessResponse([], "Post event successfully added.");
            }

            return ResponseHelper::sendSuccessResponse([], 'Successfully added post event.');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function updatePostMedia($params, $payload)
    {
        try {
            HTTPRequestValidator::validatePUTPayload($this->acceptableParamsKeys, $this->expectedPutMediaPayloadKeys, $params, $payload);

            $postID = $params['postID'];
            $mediaURL = $payload['mediaURL'];

            $response = $this->postsModel->updatePostMedia($postID, $mediaURL);

            if (!$response) {
                return ResponseHelper::sendErrorResponse("Failed to upload media file", 400);
            }

            return ResponseHelper::sendSuccessResponse([], 'Successfully uploaded media file.');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function updatePostApprovalStatus($params, $payload)
    {
        try {
            HTTPRequestValidator::validatePUTPayload($this->acceptableParamsKeys, ['approvalStatus'], $params, $payload);

            $postID = $params['postID'];
            $approvalStatus = $payload['approvalStatus'];
            $postType = $params['postType'];

            $response = $this->postsModel->updatePostApprovalStatus($postID, $approvalStatus, $postType);

            if (!$response) {
                return ResponseHelper::sendErrorResponse("Failed to update post approval status", 400);
            }

            return ResponseHelper::sendSuccessResponse([], 'Successfully updated post approval status.');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
