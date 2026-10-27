<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CloudinaryException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImageUploadRequest;
use App\Services\Cloudinary;
use Illuminate\Http\JsonResponse;

class ImageUploadController extends Controller
{
    /**
     * Receive an image dropped into the post editor and store it on Cloudinary.
     *
     * The returned "location" is the shape TinyMCE expects. It already carries a
     * width cap plus automatic format/quality so large photos stay light on the site.
     */
    public function __invoke(ImageUploadRequest $request, Cloudinary $cloudinary): JsonResponse
    {
        try {
            $image = $cloudinary->upload($request->file('file'), 'content');
        } catch (CloudinaryException $exception) {
            return response()->json(['message' => $exception->getMessage()], 502);
        }

        return response()->json([
            'location' => Cloudinary::transform($image['url'], 'c_limit,w_1600'),
        ]);
    }
}
