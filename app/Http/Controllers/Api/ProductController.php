<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        return Product::with('category')->orderBy('name')->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:255', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'image' => ['nullable', 'file', 'image'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        // Defensive defaults and casting
        $data['stock'] = isset($data['stock']) ? (int) $data['stock'] : 0;
        $data['price'] = (float) $data['price'];
        if (isset($data['cost'])) { $data['cost'] = (float) $data['cost']; }
        if (!isset($data['is_active'])) { $data['is_active'] = true; }

        // Optional upload to Cloudinary when file is provided
        if ($request->hasFile('image')) {
            $uploadedUrl = $this->uploadToCloudinary($request->file('image'));
            if ($uploadedUrl) {
                $data['image_url'] = $uploadedUrl;
            }
        }

        $product = Product::create($data);
        return response()->json($product->fresh('category'), 201);
    }

    public function show(Product $product)
    {
        return $product->load('category');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'sku' => ['sometimes', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($product->id)],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (isset($data['stock'])) { $data['stock'] = (int) $data['stock']; }
        if (isset($data['price'])) { $data['price'] = (float) $data['price']; }
        if (isset($data['cost'])) { $data['cost'] = (float) $data['cost']; }

        if ($request->hasFile('image')) {
            $uploadedUrl = $this->uploadToCloudinary($request->file('image'));
            if ($uploadedUrl) {
                $data['image_url'] = $uploadedUrl;
            }
        }

        $product->update($data);
        return $product->fresh('category');
    }

    private function uploadToCloudinary($file)
    {
        try {
            $uploadUrl = config('services.cloudinary.upload_url');
            $uploadPreset = config('services.cloudinary.upload_preset');
            $folder = config('services.cloudinary.folder');

            $multipart = [
                [ 'name' => 'file', 'contents' => fopen($file->getRealPath(), 'r') ],
                [ 'name' => 'upload_preset', 'contents' => $uploadPreset ],
            ];
            if (!empty($folder)) {
                $multipart[] = [ 'name' => 'folder', 'contents' => $folder ];
            }

            $client = new \GuzzleHttp\Client();
            $response = $client->post($uploadUrl, [ 'multipart' => $multipart ]);
            $json = json_decode((string) $response->getBody(), true);
            return $json['secure_url'] ?? $json['url'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }


    public function destroy(Product $product)
    {
        $product->delete();
        return response()->noContent();
    }
}


