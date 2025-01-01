<?php

namespace App\Controllers;

use Ramsey\Uuid\Uuid;

use App\Helpers\ResponseHelper;
use App\Validators\HTTPRequestValidator;
use App\Models\AppointmentsModel;

use RuntimeException;

class AppointmentsController
{
    private $appointmentsModel;
    private $acceptableParamsKeys = ['id', 'requestID'];
    private $expectedPostPayloadKeys = ['requestID', 'userOwnerID', 'userID', 'petID', 'appointmentDate', 'appointmentTime'];

    public function __construct($pdo)
    {
        $this->appointmentsModel = new AppointmentsModel($pdo);
    }

    public function getAllAppointments()
    {
        try {
            $response = $this->appointmentsModel->getAllAppointments();

            if (!$response) {
                return ResponseHelper::sendSuccessResponse([], 'No Appointment found');
            }

            return ResponseHelper::sendSuccessResponse($response, 'Successfully fetched all appointments');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAppointmentByID(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $appointmentID = $params['id'];

            $response = $this->appointmentsModel->getAppointmentByID($appointmentID);

            if (!$response) {
                return ResponseHelper::sendSuccessResponse([], 'No Appointment found');
            }

            return ResponseHelper::sendSuccessResponse($response, 'Successfully fetched appointment');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAppointmentByRequestID(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $requestID = $params['requestID'];
            $response = $this->appointmentsModel->getAppointmentByRequestID($requestID);

            if (!$response) {
                return ResponseHelper::sendSuccessResponse([], 'No Appointment found for the request');
            }

            return ResponseHelper::sendSuccessResponse($response, 'Successfully fetched appointment of the request');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function addNewAppointment(array $payload)
    {
        try {
            HTTPRequestValidator::validatePOSTPayload($this->expectedPostPayloadKeys, $payload);

            $uuid = Uuid::uuid7()->toString();
            $payload['id'] = $uuid;

            $response = $this->appointmentsModel->addNewAppointment($payload);

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendErrorResponse($response['message'], 401);
            }

            return ResponseHelper::sendSuccessResponse([], 'Successfully added new request appointment');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function deleteAppointmentByID(array $params)
    {
        try {
            HTTPRequestValidator::validateDELETEParameter($this->acceptableParamsKeys, $params);

            $appointmentID = $params['id'];
            $isAppointmentDeleted = $this->appointmentsModel->deleteAppointmentByID($appointmentID);

            if (!$isAppointmentDeleted) {
                return ResponseHelper::sendErrorResponse("Failed to delete appointment", 404);
            }

            return ResponseHelper::sendSuccessResponse($isAppointmentDeleted, 'Successfully deleted appointment');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
