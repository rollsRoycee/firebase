<?php

namespace App\Http\Controllers;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Illuminate\Http\Request;


class ImageController extends Controller
{
    public function uploadImage(Request $request)
    {
        // Validate the incoming image file
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust the validation rules as needed
        ]);

        // Initialize Firebase
        $serviceAccount = ServiceAccount::fromJsonFile(config('firebase.credentials_path'));
        $firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->withStorageBucket(config('firebase.storage_bucket'))
            ->create();

        // Get the image file from the request
        $imageFile = $request->file('image');

        // Generate a unique filename for the uploaded image
        $fileName = 'images/' . uniqid() . '.' . $imageFile->getClientOriginalExtension();

        // Upload the image to Firebase Storage
        $storage = $firebase->getStorage();
        $storage->getBucket()->upload($imageFile->getPathname(), ['name' => $fileName]);

        // Get the public URL of the uploaded image
        $imageUrl = $storage->getBucket()->object($fileName)->signedUrl(now()->addMinutes(60));

        // You can store the $imageUrl in your database or use it as needed

        return response()->json(['message' => 'Image uploaded successfully', 'imageUrl' => $imageUrl]);
    }

}
