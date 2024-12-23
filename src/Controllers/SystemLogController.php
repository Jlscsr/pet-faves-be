<?php

namespace App\Controllers;

use RuntimeException;
use DateTime;
use Exception;

class SystemLogController
{
    private string $logFilePath;

    public function __construct()
    {
        // Define the log file path
        $this->logFilePath = __DIR__ . '/../../logs/frontend-errors.log';

        // Ensure the log directory exists
        if (!file_exists(dirname($this->logFilePath))) {
            mkdir(dirname($this->logFilePath), 0755, true);
        }
    }

    /**
     * Logs frontend errors to a file in a clean, professional format.
     * 
     * @param array $payload The error details sent from the frontend.
     * @return void
     * @throws RuntimeException
     */
    public function logFrontendError($payload): void
    {
        try {
            // Validate payload format
            if (empty($payload['message']) || empty($payload['timestamp'])) {
                throw new RuntimeException("Invalid error payload.");
            }

            // Format log entry
            $logEntry = $this->formatLogEntry($payload);

            // Write log to file
            file_put_contents($this->logFilePath, $logEntry, FILE_APPEND);
        } catch (Exception $e) {
            // Handle internal logging errors (optional)
            error_log("Logging error: " . $e->getMessage());
        }
    }

    /**
     * Formats the log entry for the error.
     * 
     * @param array $payload
     * @return string
     */
    private function formatLogEntry(array $payload): string
    {
        $type = $payload['type'] ?? 'Unknown Type';
        $timestamp = $payload['timestamp'] ?? (new DateTime())->format(DateTime::ATOM);
        $message = $payload['message'] ?? 'No message provided';
        $requestName = $payload['requestName'] ?? 'No request name provided';
        $stack = $payload['stack'] ?? 'No stack trace available';
        $filename = $payload['filename'] ?? 'No filename specified';
        $userAgent = $payload['userAgent'] ?? 'Unknown User Agent';
        $additionalInfo = json_encode($payload['additionalInfo'] ?? [], JSON_PRETTY_PRINT);

        // Create a clean, informative log format
        return <<<LOG
                ===============================================================
                [Type]: $type
                [Timestamp]: $timestamp
                [Request Name]: $requestName
                [Filename]: $filename
                [Message]: $message
                [User Agent]: $userAgent
                [Additional Info]: $additionalInfo
                [Stack Trace]: $stack
                ===============================================================
                LOG;
    }
}
