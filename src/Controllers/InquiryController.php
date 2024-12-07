<?php

namespace App\Controllers;

use RuntimeException;

use App\Helpers\EmailHelper;
use App\Helpers\ResponseHelper;

class InquiryController
{
    public function sendNewInquiry(array $payload)
    {
        try {
            $userName = $payload['fullName'];
            $userEmail = $payload['email'];
            $phoneNo = $payload['phoneNum'];
            $message = $payload['message'];

            $emailHelper = new EmailHelper();
            $emailHelper->sendInquiryEmail($userName, $userEmail, $phoneNo, $message);

            return ResponseHelper::sendSuccessResponse([], 'Inquiry sent successfully');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
