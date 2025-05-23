<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/AdminService.php';
require_once __DIR__ . '/../TemplateEngine.php';

class AdminController
{
    private AdminService $adminService;

    public function __construct()
    {
        $this->adminService = new AdminService();
    }

    public function deleteFile(array $input): void
    {
        try {
            $filePath = $input['path'] ?? null;
            if (!$filePath) {
                echo json_encode(['status' => 'error', 'message' => 'File path is missing.']);
                return;
            }
            $result = $this->adminService->deleteFile($filePath);
            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }


    public function previewFile(array $input): void
    {
        $response = $this->adminService->previewFile($input);
        if ($response['status'] === 'success') {
            echo json_encode($response['content']);
        } else {
            echo json_encode($response);
        }
    }


    public function uploadFiles(array $input): void
    {
        $this->adminService->uploadFiles($input);
    }

    public function downloadFile(array $input): void
    {
        $this->adminService->downloadFile($input);
    }

    public function listFiles(array $input): void
    {
        $response = $this->adminService->listFiles($input);

        if ($response['status'] === 'success') {
            $templateEngine = new TemplateEngine();
            echo $templateEngine->render('/var/www/WebTech/templates/directory-listing.html', $response);
        } else {
            echo json_encode($response);
        }
    }

    public function getFileContent(array $input): void
    {
        echo json_encode($this->adminService->getFileContent($input));
    }

    public function saveFileContent(array $input): void
    {
        echo json_encode($this->adminService->saveFileContent($input));
    }
}
