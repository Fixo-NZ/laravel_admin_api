<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * GET /api/jobs/categories
     * 
     * Fetch all service categories
     */
    public function index(Request $request)
    {
        $query = ServiceCategory::query();

        $categories = $query->whereIn('status', ['active', 'inactive'])->get();

        $data = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'icon' => $category->icon,
                'status' => $category->status,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'All Service Categories fetched successfully',
            'data' => $data,
        ]);
    }

    /**
     * GET /api/jobs/categories/{id}
     * 
     * Fetch a single category
     */
    public function indexSpecificCategory(Request $request, $id)
    {
        $category = ServiceCategory::with('services')->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Service category not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Specific Category fetched successfully',
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'icon' => $category->icon,
                'status' => $category->status,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ],
        ]);
    }

    /**
     * GET /api/jobs/categories/{id}/services
     * 
     * Fetch all services under a specific category
     */
    public function indexSpecificCategoryServices($categoryId)
    {
        $category = ServiceCategory::find($categoryId);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $services = $category->services;

        return response()->json([
            'success' => true,
            'message' => 'Specific Service fetched successfully',
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                ],
                'services' => $services->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'description' => $s->description,
                    'status' => $s->status,
                    'created_at' => $s->created_at,
                    'updated_at' => $s->updated_at,
                ]),
            ],
        ]);
    }

    /**
     * GET /api/jobs/services
     * 
     * Fetch all services across all categories
     */
    public function indexService()
    {
        $services = Service::with('category')->get();

        return response()->json([
            'success' => true,
            'message' => 'All services fetched successfully',
            'data' => $services->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'description' => $s->description,
                'category' => $s->category?->name,
                'status' => $s->status,
                'created_at' => $s->created_at,
                'updated_at' => $s->updated_at,
            ]),
        ]);
    }

    /**
     * GET /api/jobs/services/{id}
     * 
     * Fetch a specific service with its category
     */
    public function indexSpecificService($id)
    {
        $service = Service::with('category')->find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Specific Service fetched successfully',
            'data' => [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'status' => $service->status,
                'category' => [
                    'id' => $service->category?->id,
                    'name' => $service->category?->name,
                    'description' => $service->category?->description,
                ],
                'created_at' => $service->created_at,
                'updated_at' => $service->updated_at,
            ],
        ]);
    }
}
