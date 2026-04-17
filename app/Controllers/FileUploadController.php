<?php

namespace App\Controllers;

use BaseApi\Controllers\Controller;
use BaseApi\Http\JsonResponse;
use BaseApi\Http\UploadedFile;
use BaseApi\Http\Attributes\ResponseType;
use BaseApi\Http\Attributes\Tag;
use BaseApi\Http\Validation\Attributes\File;
use BaseApi\Http\Validation\Attributes\Mimes;
use BaseApi\Http\Validation\Attributes\Size;
use BaseApi\Storage\Storage;

#[Tag('Files')]
class FileUploadController extends Controller
{
    #[File]
    #[Mimes(['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'])]
    #[Size(5)] // 5MB max
    public ?UploadedFile $file = null;

    #[ResponseType([
        'path' => 'string',
        'url' => 'string',
        'size' => 'int',
        'type' => 'string'
    ])]
    public function post(): JsonResponse
    {
        // Handle different POST endpoints based on path
        $path = $this->request->path;
        
        return match ($path) {
            '/files/upload' => $this->handleUpload(),
            '/files/upload-public' => $this->uploadPublic(),
            '/files/upload-custom' => $this->uploadWithCustomName(),
            default => JsonResponse::notFound('接口不存在')
        };
    }
    
    private function handleUpload(): JsonResponse
    {
        // Validate that file is present
        if (!$this->file instanceof UploadedFile) {
            return JsonResponse::badRequest('请选择要上传的文件');
        }
        
        // Store the file with auto-generated name (validation handled automatically via attributes)
        $path = $this->file->store('uploads');

        // Get the public URL for the file
        $url = Storage::url($path);

        return JsonResponse::created([
            'path' => $path,
            'url' => $url,
            'size' => $this->file->getSize(),
            'type' => $this->file->getMimeType(),
            'original_name' => $this->file->name
        ]);
    }

    /**
     * Upload a file to public storage (accessible via web).
     */
    private function uploadPublic(): JsonResponse
    {
        // Validate that file is present
        if (!$this->file instanceof UploadedFile) {
            return JsonResponse::badRequest('请选择要上传的文件');
        }
        
        // Store the file in public storage (validation handled automatically via attributes)
        $path = $this->file->storePublicly('public/uploads');

        // Get the public URL for the file
        $url = Storage::disk('public')->url($path);

        return JsonResponse::created([
            'path' => $path,
            'url' => $url,
            'size' => $this->file->getSize(),
            'type' => $this->file->getMimeType(),
            'original_name' => $this->file->name
        ]);
    }

    /**
     * Upload a file with a custom name.
     */
    private function uploadWithCustomName(): JsonResponse
    {
        // Validate that file is present
        if (!$this->file instanceof UploadedFile) {
            return JsonResponse::badRequest('请选择要上传的文件');
        }
        
        // Generate a custom filename (validation handled automatically via attributes)
        $extension = $this->file->getExtension();
        $customName = 'custom_' . date('Y_m_d_H_i_s') . '.' . $extension;

        // Store the file with custom name
        $path = $this->file->storeAs('uploads', $customName);

        // Get the public URL for the file
        $url = Storage::url($path);

        return JsonResponse::created([
            'path' => $path,
            'url' => $url,
            'size' => $this->file->getSize(),
            'type' => $this->file->getMimeType(),
            'original_name' => $this->file->name,
            'stored_name' => $customName
        ]);
    }

    public function get(): JsonResponse
    {
        // Handle different GET endpoints based on path
        $path = $this->request->path;
        
        return match ($path) {
            '/files/info' => $this->getFileInfo(),
            default => JsonResponse::notFound('接口不存在')
        };
    }

    /**
     * Get information about a stored file.
     */
    private function getFileInfo(): JsonResponse
    {
        $path = $this->request->query['path'] ?? '';

        if (!$path || !Storage::exists($path)) {
            return JsonResponse::notFound('文件不存在');
        }

        return JsonResponse::ok([
            'path' => $path,
            'url' => Storage::url($path),
            'size' => Storage::size($path),
            'exists' => Storage::exists($path)
        ]);
    }

    public function delete(): JsonResponse
    {
        return $this->deleteFile();
    }

    /**
     * Delete a stored file.
     */
    private function deleteFile(): JsonResponse
    {
        $path = $this->request->body['path'] ?? '';

        if (!$path || !Storage::exists($path)) {
            return JsonResponse::notFound('文件不存在');
        }

        $deleted = Storage::delete($path);

        if ($deleted) {
            return JsonResponse::ok(['message' => '文件删除成功']);
        }

        return JsonResponse::error('删除文件失败');
    }
}
