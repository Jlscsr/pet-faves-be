<?php

namespace App\Controllers;

use Ramsey\Uuid\Uuid;

use App\Helpers\ResponseHelper;

use App\Validators\HTTPRequestValidator;

use App\Models\ReportsModel;

use RuntimeException;

class ReportsController
{
    private $reportsModel;

    public function __construct($pdo)
    {
        $this->reportsModel = new ReportsModel($pdo);
    }

    public function getAllReports()
    {
        try {
            $reports = $this->reportsModel->getAllReports();

            if (empty($reports)) {
                return ResponseHelper::sendSuccessResponse([], 'No reports found');
            }

            return ResponseHelper::sendSuccessResponse($reports, 'Pets found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function generateReports($payload)
    {
        try {

            $startDate = $payload['startDate'];
            $endDate = $payload['endDate'];

            $response = $this->reportsModel->generateReports($startDate, $endDate);

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendSuccessResponse([], 'No reports generated');
            }

            return ResponseHelper::sendSuccessResponse($response['data'], 'Reports generated');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
