<?php
namespace SalesDashboard\Service;

class JsonImporterService
{
    private $uploadDir;
    private $allowedFileTypes = ['application/json'];

    public function __construct($uploadDir)
    {
        $this->uploadDir = $uploadDir;
    }

    public function importJson($jsonFile)
    {
        // Validate file type
        if (!$this->isValidFileType($jsonFile['type'])) {
            return ["success" => false, "message" => "Invalid file type. Only JSON files are allowed."];
        }

        // Validate file size (adjust the maximum size as needed)
        if ($jsonFile['size'] > 5 * 1024 * 1024) {
            return ["success" => false, "message" => "File size exceeds the maximum allowed limit (5MB)."];
        }

        if ($jsonFile['error'] == UPLOAD_ERR_OK) {
            $tempFilePath = $jsonFile['tmp_name'];
            $jsonData = file_get_contents($tempFilePath);

            // Validate JSON format
            if (!$this->isValidJson($jsonData)) {
                return ["success" => false, "message" => "Invalid JSON format."];
            }

            return ["success" => true, "message" => "JSON data successfully imported!", "data" => $jsonData];
        } else {
            return ["success" => false, "message" => "Error uploading JSON file."];
        }
    }


    private function isValidFileType($fileType)
    {
        return in_array($fileType, $this->allowedFileTypes);
    }

    private function isValidJson($jsonData)
    {
        json_decode($jsonData);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
