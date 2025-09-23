<?php
// app/Services/ImageService.php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class ImageService
{
    /**
     * Handle the image upload, resizing, and storage.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param int $width
     * @param int $height
     * @return string The path to the stored image.
     * @throws Exception If the image processing fails.
     */
    public function uploadAndResize(UploadedFile $file, string $directory, int $width, int $height): string
    {
        try {
            // Create directory if it doesn't exist
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }

            // Generate a unique filename
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $directory . '/' . $filename;

            // Resize and save the image using Intervention Image
            $image = Image::make($file->getRealPath());
            $image->fit($width, $height, function ($constraint) {
                $constraint->upsize();
            });
            
            // Save the image to the specified path
            Storage::put($path, (string) $image->encode());

            return $path;
        } catch (Exception $e) {
            Log::error('Image upload and resize failed: ' . $e->getMessage());
            throw new Exception('Failed to process the image.');
        }
    }

    /**
     * Delete an image from storage.
     *
     * @param string $path
     * @return bool
     */
    public function deleteImage(string $path): bool
    {
        if (Storage::exists($path)) {
            return Storage::delete($path);
        }
        return false;
    }

    public function handleRoomImageUpload($requestImage, $currentImage = null)
    {
        if ($currentImage) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $currentImage));
        }
        
        if ($requestImage) {
            $imagePath = $requestImage->store('room_images', 'public');
            return '/storage/' . $imagePath;
        }
        
        return $currentImage;
    }
}