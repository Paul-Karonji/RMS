<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Upload a unit photo.
     */
    public function uploadUnitPhoto(UploadedFile $file, string $unitId): string
    {
        $this->validateImage($file);
        
        $filename = $this->generateFilename($file, 'unit-photo');
        $path = "units/{$unitId}/photos/{$filename}";
        
        Storage::disk(config('filesystems.default'))->put($path, file_get_contents($file));
        
        return $path;
    }

    /**
     * Upload an expense receipt.
     */
    public function uploadExpenseReceipt(UploadedFile $file, string $expenseId): string
    {
        $this->validateDocument($file);
        
        $filename = $this->generateFilename($file, 'receipt');
        $path = "expenses/{$expenseId}/receipts/{$filename}";
        
        Storage::disk(config('filesystems.default'))->put($path, file_get_contents($file));
        
        return $path;
    }

    /**
     * Upload a maintenance request photo.
     */
    public function uploadMaintenancePhoto(UploadedFile $file, string $requestId): string
    {
        $this->validateImage($file);
        
        $filename = $this->generateFilename($file, 'maintenance');
        $path = "maintenance/{$requestId}/photos/{$filename}";
        
        Storage::disk(config('filesystems.default'))->put($path, file_get_contents($file));
        
        return $path;
    }

    /**
     * Upload a property document.
     */
    public function uploadPropertyDocument(UploadedFile $file, string $propertyId): string
    {
        $this->validateDocument($file);
        
        $filename = $this->generateFilename($file, 'document');
        $path = "properties/{$propertyId}/documents/{$filename}";
        
        Storage::disk(config('filesystems.default'))->put($path, file_get_contents($file));
        
        return $path;
    }

    /**
     * Delete a file.
     */
    public function deleteFile(string $path): bool
    {
        return Storage::disk(config('filesystems.default'))->delete($path);
    }

    /**
     * Get a signed URL for a file (60 minutes expiry).
     */
    public function getSignedUrl(string $path, int $expirationMinutes = 60): string
    {
        $disk = config('filesystems.default');
        
        if ($disk === 'local' || $disk === 'public') {
            return Storage::url($path);
        }
        
        // For S3/R2, generate temporary signed URL
        return Storage::disk($disk)->temporaryUrl(
            $path,
            now()->addMinutes($expirationMinutes)
        );
    }

    /**
     * Get file URL.
     */
    public function getUrl(string $path): string
    {
        return Storage::disk(config('filesystems.default'))->url($path);
    }

    /**
     * Validate image file.
     */
    protected function validateImage(UploadedFile $file): void
    {
        $maxSize = 5 * 1024 * 1024; // 5MB
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        
        if ($file->getSize() > $maxSize) {
            throw new \Exception('File size must not exceed 5MB');
        }
        
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \Exception('File must be an image (JPEG, PNG, WebP)');
        }
    }

    /**
     * Validate document file.
     */
    protected function validateDocument(UploadedFile $file): void
    {
        $maxSize = 10 * 1024 * 1024; // 10MB
        $allowedMimes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/jpg',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        if ($file->getSize() > $maxSize) {
            throw new \Exception('File size must not exceed 10MB');
        }
        
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \Exception('Invalid file type. Allowed: PDF, Images, Word documents');
        }
    }

    /**
     * Generate unique filename.
     */
    protected function generateFilename(UploadedFile $file, string $prefix): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');
        $random = Str::random(8);
        
        return "{$prefix}-{$timestamp}-{$random}.{$extension}";
    }

    /**
     * Upload multiple files.
     */
    public function uploadMultiple(array $files, string $type, string $id): array
    {
        $paths = [];
        
        foreach ($files as $file) {
            $paths[] = match($type) {
                'unit_photo' => $this->uploadUnitPhoto($file, $id),
                'expense_receipt' => $this->uploadExpenseReceipt($file, $id),
                'maintenance_photo' => $this->uploadMaintenancePhoto($file, $id),
                'property_document' => $this->uploadPropertyDocument($file, $id),
                default => throw new \Exception('Invalid upload type'),
            };
        }
        
        return $paths;
    }
}
