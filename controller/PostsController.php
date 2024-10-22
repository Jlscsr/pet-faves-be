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

    public function getAllPosts()
    {
        try {
            $limit = (int) $_GET['limit'] ?? 0;
            $offset = (int) $_GET['offset'] ?? 0;

            $response = $this->postsModel->getAllPosts($offset, $limit);

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
}
