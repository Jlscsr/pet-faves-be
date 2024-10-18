<?php

use Helpers\ResponseHelper;
use Helpers\HeaderHelper;

use Models\AppointmentsModel;

class AppointmentsController
{
    private $appointmentsModel;

    public function __construct($pdo)
    {
        $this->appointmentsModel = new AppointmentsModel($pdo);

        HeaderHelper::setResponseHeaders();
    }

    public function getRequestAppointmentByRequestID($param)
    {
        try {
            if (empty($param) || !isset($params['id'])) {
                ResponseHelper::sendErrorResponse("Invalid or missing id parameter", 400);
                return;
            }

            $id = (int) $param['id'];
            $response = $this->appointmentsModel->getRequestAppointmentByRequestID($id);

            if (!$response) {
                ResponseHelper::sendErrorResponse("Appointment not found", 404);
                exit;
            }

            ResponseHelper::sendSuccessResponse($response, 'Successfully fetched appointment of the request');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function addNewRequestAppointment($payload)
    {
        try {
            if (empty($payload)) {
                ResponseHelper::sendErrorResponse("Payload is empty", 400);
                return;
            }

            $response = $this->appointmentsModel->addNewRequestAppointment($payload);

            if (!$response) {
                ResponseHelper::sendErrorResponse("Request not found", 404);
                exit;
            }

            ResponseHelper::sendSuccessResponse($response, 'Successfully added new request appointment');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
