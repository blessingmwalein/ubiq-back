<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VideoAsset;
use App\Models\ContentItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FileUploadController extends Controller
{
    /**
     * Upload an image file.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:102400', // 100MB max
            'folder' => 'nullable|string',
            'content_id' => 'nullable|exists:content_items,id',
            'type' => 'nullable|in:poster,thumbnail,backdrop',
        ]);

        $file = $request->file('file');
        $folder = $request->input('folder', 'images');

        // Store file using the public disk directly (without 'public/' prefix in path)
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $relativePath = "{$folder}/{$filename}";

        // Store in public disk
        Storage::disk('public')->putFileAs($folder, $file, $filename);

        // Generate URL (this will correctly prepend /storage/)
        $url = Storage::disk('public')->url($relativePath);

        return response()->json([
            'success' => true,
            'url' => $url,
            'path' => $url,
            'filename' => $filename,
        ]);
    }

    /**
     * Upload a video file with chunked support.
     */
    public function uploadVideo(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-ms-wmv,video/webm|max:5120000', // 5GB max
            'folder' => 'nullable|string',
            'content_id' => 'nullable|exists:content_items,id',
        ]);

        $file = $request->file('file');
        $folder = $request->input('folder', 'videos');

        // Generate unique filename
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $relativePath = "{$folder}/{$filename}";

        // Store in public disk
        Storage::disk('public')->putFileAs($folder, $file, $filename);
        $storagePath = Storage::disk('public')->path($relativePath);

        // Get video metadata
        $metadata = $this->getVideoMetadata($storagePath);

        // Create video asset record if content_id is provided
        $videoAsset = null;
        if ($request->filled('content_id')) {
            // Generate unique rendition key based on resolution or use 'original'
            $resolution = $metadata['resolution'] ?? 'unknown';
            $renditionKey = strpos($resolution, 'x') !== false
                ? explode('x', $resolution)[1] . 'p' // e.g., '1080p' from '1920x1080'
                : 'original';

            $videoAsset = VideoAsset::create([
                'content_item_id' => $request->input('content_id'),
                'rendition_key' => $renditionKey,
                'hls_manifest_key' => $relativePath, // Store without 'public/' prefix
                'hls_playlist_path' => dirname($relativePath), // Directory path
                'file_size_mb' => round($file->getSize() / 1024 / 1024, 2),
                'resolution' => $metadata['resolution'] ?? null,
                'bitrate' => $metadata['bitrate'] ?? null,
                'status' => 'ready',
            ]);

            // Update content item duration if not set
            $contentItem = ContentItem::find($request->input('content_id'));
            if ($contentItem && !$contentItem->duration_seconds && isset($metadata['duration'])) {
                $contentItem->update(['duration_seconds' => (int)$metadata['duration']]);
            }
        }

        return response()->json([
            'success' => true,
            'url' => Storage::disk('public')->url($relativePath),
            'path' => Storage::disk('public')->url($relativePath),
            'filename' => $filename,
            'size' => $file->getSize(),
            'metadata' => $metadata,
            'video_asset_id' => $videoAsset?->id,
        ]);
    }

    /**
     * Handle chunked video upload.
     */
    public function uploadChunk(Request $request)
    {
        $request->validate([
            'chunk' => 'required|file',
            'chunkIndex' => 'required|integer',
            'totalChunks' => 'required|integer',
            'uploadId' => 'required|string',
            'filename' => 'required|string',
        ]);

        $uploadId = $request->input('uploadId');
        $chunkIndex = $request->input('chunkIndex');
        $totalChunks = $request->input('totalChunks');
        $filename = $request->input('filename');

        // Store chunk temporarily
        $chunkPath = "chunks/{$uploadId}";
        $request->file('chunk')->storeAs($chunkPath, "chunk_{$chunkIndex}");

        // Check if all chunks are uploaded
        $uploadedChunks = Storage::files($chunkPath);

        if (count($uploadedChunks) === $totalChunks) {
            // Merge all chunks
            $finalPath = $this->mergeChunks($uploadId, $filename, $totalChunks);

            // Clean up chunks
            Storage::deleteDirectory($chunkPath);

            return response()->json([
                'success' => true,
                'complete' => true,
                'path' => Storage::url($finalPath),
                'filename' => basename($finalPath),
            ]);
        }

        return response()->json([
            'success' => true,
            'complete' => false,
            'uploadedChunks' => count($uploadedChunks),
            'totalChunks' => $totalChunks,
        ]);
    }

    /**
     * Merge uploaded chunks into a single file.
     */
    private function mergeChunks(string $uploadId, string $originalFilename, int $totalChunks): string
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $filename = Str::random(40) . '.' . $extension;
        $finalPath = "public/videos/{$filename}";

        $outputPath = Storage::path($finalPath);
        $outputHandle = fopen($outputPath, 'wb');

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = Storage::path("chunks/{$uploadId}/chunk_{$i}");
            $chunkHandle = fopen($chunkPath, 'rb');

            while (!feof($chunkHandle)) {
                fwrite($outputHandle, fread($chunkHandle, 8192));
            }

            fclose($chunkHandle);
        }

        fclose($outputHandle);

        return $finalPath;
    }

    /**
     * Get video metadata (duration, dimensions, etc.)
     */
    private function getVideoMetadata(string $path): array
    {
        // This is a placeholder - you would typically use FFmpeg or similar
        // For now, return basic info

        try {
            if (!file_exists($path)) {
                return [];
            }

            $size = filesize($path);

            // You can integrate FFmpeg here for proper video metadata
            // Example: exec("ffprobe -v quiet -print_format json -show_format -show_streams '$path'", $output);

            return [
                'size' => $size,
                'mime_type' => mime_content_type($path),
                // Add more metadata when FFmpeg is integrated
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Delete an uploaded file.
     */
    public function deleteFile(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->input('path');

        // Remove /storage prefix if present
        $path = str_replace('/storage/', '', $path);
        $fullPath = "public/{$path}";

        if (Storage::exists($fullPath)) {
            Storage::delete($fullPath);

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'File not found',
        ], 404);
    }

    /**
     * Delete a video asset and its file.
     */
    public function deleteVideoAsset(string $id)
    {
        try {
            $videoAsset = VideoAsset::findOrFail($id);

            // Delete the physical file if it exists
            $path = $videoAsset->hls_manifest_key;
            if ($path) {
                // Remove /storage prefix if present
                $path = str_replace('/storage/', '', $path);
                $fullPath = str_replace('public/', '', $path);
                $fullPath = "public/{$fullPath}";

                if (Storage::exists($fullPath)) {
                    Storage::delete($fullPath);
                }
            }

            // Delete the database record
            $videoAsset->delete();

            return response()->json([
                'success' => true,
                'message' => 'Video deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete video: ' . $e->getMessage(),
            ], 500);
        }
    }
}
