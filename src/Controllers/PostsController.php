<?php

namespace App\Controllers;

use Ramsey\Uuid\Uuid;

use App\Models\PostsModel;

use App\Helpers\ResponseHelper;

use App\Validators\HTTPRequestValidator;

use RuntimeException;

class PostsController
{
    private $pdo;
    private $postsModel;
    private $acceptableParamsKeys = ['approvalStatus', 'postID', 'postType', 'typeOfPost', 'userID', 'status', 'id'];
    private $commonPostPayloadKeys = ['userID', 'postDescription', 'status', 'postType'];
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

    public function getAllPostsPetFeeds(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $approvalStatus = $params['approvalStatus'];

            $response = $this->postsModel->getAllPostsPetFeeds($approvalStatus);

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

            $response = $this->postsModel->getAllPostsByTypeOfPost($typeOfPost);

            if (!$response) {
                return ResponseHelper::sendSuccessResponse([], "No posts found.");
            }

            return ResponseHelper::sendSuccessResponse($response, 'Successfully fetched posts.');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAllPostsByIDAndTypeOfPost(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $id = $params['id'];
            $postType = $params['typeOfPost'];

            $response = $this->postsModel->getAllPostsByIDAndTypeOfPost($id, $postType);

            if (!$response) {
                return ResponseHelper::sendSuccessResponse([], "No post found.");
            }

            return ResponseHelper::sendSuccessResponse($response, 'Successfully fetched post.');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAllPostsByUserIDAndStatus(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $userID = $params['userID'];
            $status = $params['approvalStatus'];

            $response = $this->postsModel->getAllPostsByUserIDAndStatus($userID, $status);

            if (!$response) {
                return ResponseHelper::sendSuccessResponse([], "No posts found.");
            }

            return ResponseHelper::sendSuccessResponse($response, 'Successfully fetched posts.');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }


    public function addNewPost(array $payload)
    {
        try {
            HTTPRequestValidator::validatePOSTPayload($this->expectedPostPayloadKeys, $payload);

            $uuid = Uuid::uuid7()->toString();
            $payload['id'] = $uuid;

            $response = $this->postsModel->addNewPost($payload);

            if (!$response) {
                return ResponseHelper::sendSuccessResponse([], "Post successfully added.");
            }

            return ResponseHelper::sendSuccessResponse($response, 'Successfully added post.');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function addNewPostMedia($payload)
    {
        try {
            HTTPRequestValidator::validatePOSTPayload($this->expectedPostMediaPayloadKeys, $payload);

            $uuid = Uuid::uuid7()->toString();
            $payload['id'] = $uuid;

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

            $uuid = Uuid::uuid7()->toString();
            $payload['id'] = $uuid;

            $response = $this->postsModel->addNewEventPost($payload);

            if (!$response) {
                return ResponseHelper::sendSuccessResponse([], "Post event successfully added.");
            }

            return ResponseHelper::sendSuccessResponse($response, 'Successfully added post event.');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function updatePostMedia(array $params, array $payload)
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

    public function updatePostApprovalStatus(array $params, array $payload)
    {
        try {
            HTTPRequestValidator::validatePUTPayload($this->acceptableParamsKeys, ['status'], $params, $payload);

            $postID = $params['postID'];
            $approvalStatus = $payload['status'];
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
