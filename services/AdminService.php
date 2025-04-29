<?php
declare(strict_types=1);
// Configuration
define('UPLOAD_DIR', '/var/www/WebTech/uploads/');

class AdminService
{
    public function deleteFile(string $filePath): array
    {
        if (!$filePath || !file_exists($filePath)) {
            return [
                'status' => 'error',
                'message' => 'Invalid input: File does not exist at the specified path.'
            ];
        }
        if (!is_writable($filePath)) {
            return [
                'status' => 'error',
                'message' => 'File is not writable or deletable. Please check permissions.'
            ];
        }
        try {
            if (unlink($filePath)) {
                return [
                    'status' => 'success',
                    'message' => "File successfully deleted: $filePath"
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Failed to delete the file. An unknown error occurred.'
                ];
            }
        } catch (Exception $e) {
            error_log("Error deleting file: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'An exception occurred: ' . $e->getMessage()
            ];
        }
    }



    public function previewFile(array $input): array
    {
        $filePath = $input['path'] ?? null;

        if ($filePath === null || !file_exists($filePath)) {
            return [
                'status' => 'error',
                'message' => 'File not found or invalid path'
            ];
        }
        $content = file_get_contents($filePath);

        return [
            'status' => 'success',
            'fileName' => basename($filePath),
            'content' => $content
        ];
    }


    public function uploadFiles(array $input): void
    {
        if (!isset($_FILES['file'])) {
            echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
            return;
        }

        $file = $_FILES['file'];
        $uploadDirectory = '/var/www/WebTech/uploads/';
        $targetPath = $uploadDirectory . basename($file['name']);
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            chmod($targetPath, 0777);
            chown($targetPath, 'www-data');
            chgrp($targetPath, 'www-data');

            echo json_encode(['status' => 'success', 'message' => 'File uploaded successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload file']);
        }

    }


    public function downloadFile(array $input): void
    {
        $fileName = $input['get']['file'] ?? '';
        if (empty($fileName)) {
            throw new Exception('No file specified');
        }

        $filePath = UPLOAD_DIR . $fileName;
        if (!file_exists($filePath)) {
            throw new Exception('File not found');
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . mime_content_type($filePath));
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));

        ob_clean();
        flush();
        readfile($filePath);
        exit;
    }

    public function listFiles(array $input): array
    {
        $requestedPath = $input['path'];
        $realPath = realpath($requestedPath);

        if ($realPath === false || !is_dir($realPath)) {
            return [
                'status' => 'error',
                'message' => 'Invalid or non-existing directory'
            ];
        }

        $directories = [];
        $files = [];
        $items = @scandir($realPath);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $itemPath = $realPath . DIRECTORY_SEPARATOR . $item;
            if (is_dir($itemPath) && $item !== 'admin') {
                $directories[] = $item;
            } elseif (is_file($itemPath) && is_readable($itemPath)) {
                $files[] = $item;
            }
        }

        return [
            'status' => 'success',
            'path' => $realPath,
            'parentPath' => dirname($realPath),
            'directories' => $directories,
            'files' => $files
        ];
    }


    public function getFileContent(array $input): array
    {
        $fileName = $input['get']['file'] ?? '';
        if (empty($fileName)) {
            throw new Exception('No file specified');
        }

        $filePath = UPLOAD_DIR . $fileName;
        if (!file_exists($filePath)) {
            throw new Exception('File not found');
        }

        $editableTypes = ['text/plain', 'application/json', 'text/html', 'text/css', 'text/javascript'];
        $fileType = mime_content_type($filePath);

        if (!in_array($fileType, $editableTypes)) {
            throw new Exception('File type not editable');
        }

        return [
            'status' => 'success',
            'content' => file_get_contents($filePath),
            'type' => $fileType
        ];
    }

    public function saveFileContent(array $input): array
    {
        $fileName = $input['post']['file'] ?? '';
        $content = $input['post']['content'] ?? '';

        if (empty($fileName)) {
            throw new Exception('No file specified');
        }

        $filePath = UPLOAD_DIR . $fileName;
        if (!file_exists($filePath)) {
            throw new Exception('File not found');
        }

        $editableTypes = ['text/plain', 'application/json', 'text/html', 'text/css', 'text/javascript'];
        $fileType = mime_content_type($filePath);

        if (!in_array($fileType, $editableTypes)) {
            throw new Exception('File type not editable');
        }

        if (file_put_contents($filePath, $content) !== false) {
            return [
                'status' => 'success',
                'message' => 'File saved successfully',
                'modified' => date('Y-m-d H:i:s', filemtime($filePath))
            ];
        } else {
            throw new Exception('Failed to save file');
        }
    }

}
