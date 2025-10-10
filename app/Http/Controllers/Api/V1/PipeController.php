<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Pipe;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PipeController extends Controller
{
    /**
     * Display a listing of pipes
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Pipe::withTrashed();

            // Search functionality
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('farmer_id', 'like', "%{$search}%")
                      ->orWhere('farmer_unique_id', 'like', "%{$search}%")
                      ->orWhere('plot_no', 'like', "%{$search}%")
                      ->orWhere('farmerPlotUniqueid', 'like', "%{$search}%");
                });
            }

            // Filter by farmer
            if ($request->has('farmer_id')) {
                $query->where('farmer_id', $request->farmer_id);
            }

            // Filter by year
            if ($request->has('select_year')) {
                $query->where('select_year', $request->select_year);
            }

            // Filter by season
            if ($request->has('select_season')) {
                $query->where('select_season', $request->select_season);
            }

            // Show only deleted records
            if ($request->has('deleted_only') && $request->deleted_only) {
                $query->onlyTrashed();
            }

            // Show only active records
            if ($request->has('active_only') && $request->active_only) {
                $query->whereNull('deleted_at');
            }

            $pipes = $query->orderBy('created_at', 'desc')->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $pipes,
                'message' => 'Pipes retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving pipes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created pipe
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // $validator = Validator::make($request->all(), [
            //     'farmer_id' => 'required|string|max:255',
            //     'farmer_unique_id' => 'required|string|max:255',
            //     'select_year' => 'required|integer|min:2000|max:2100',
            //     'select_season' => 'required|string|max:100',
            //     'plot_no' => 'required|string|max:100',
            //     'pipe_count' => 'required|integer|min:1',
            //     'lat' => 'required|numeric|between:-90,90',
            //     'lng' => 'required|numeric|between:-180,180',
            //     'farmerPlotUniqueid' => 'required|string|max:255',
            //     'pipeimage1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            //     'pipeimage2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            //     'current_date' => 'required|date',
            //     'current_time' => 'required|date_format:H:i:s'
            // ]);

            // if ($validator->fails()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Validation failed',
            //         'errors' => $validator->errors()
            //     ], 422);
            // }

            $data = $request->all();

            // Handle image uploads
            // pipeimage1 ko S3 par upload karne ka code (PipeIntallationController ke example jaise)
            // pipeimage1 aur pipeimage2 dono ko S3 par upload karna hai, aur images column me JSON store karna hai
            $imageFields = ['pipeimage1', 'pipeimage2'];
            $imagesArray = [];

            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    $image = $request->file($field);
                    $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                    $path = \Storage::disk('s3')->putFileAs(
                        config('storagesystems.path') . '/' . $request->farmer_unique_id . '/' . $request->farmerPlotUniqueid . '/P' . $request->plot_no . '/PipeInstallation',
                        $image,
                        $filename
                    );

                    $imagesArray[$field] = \Storage::disk('s3')->url($path);
                }
            }

            // images column me json store karo, key image ki request ki key aur value me image ka path
            if (!empty($imagesArray)) {
                $data['images'] = json_encode($imagesArray);
            }

            $pipe = Pipe::create($data);

            return response()->json([
                'success' => true,
                'data' => $pipe,
                'message' => 'Pipe created successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating pipe: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified pipe
     */
    public function show($id): JsonResponse
    {
        try {
            $pipe = Pipe::withTrashed()->find($id);

            if (!$pipe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pipe not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $pipe,
                'message' => 'Pipe retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving pipe: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified pipe
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $pipe = Pipe::find($id);

            if (!$pipe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pipe not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'farmer_id' => 'sometimes|required|string|max:255',
                'farmer_unique_id' => 'sometimes|required|string|max:255',
                'select_year' => 'sometimes|required|integer|min:2000|max:2100',
                'select_season' => 'sometimes|required|string|max:100',
                'plot_no' => 'sometimes|required|string|max:100',
                'pipe_count' => 'sometimes|required|integer|min:1',
                'lat' => 'sometimes|required|numeric|between:-90,90',
                'lng' => 'sometimes|required|numeric|between:-180,180',
                'farmerPlotUniqueid' => 'sometimes|required|string|max:255',
                'pipeimage1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'pipeimage2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'current_date' => 'sometimes|required|date',
                'current_time' => 'sometimes|required|date_format:H:i:s'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();

            // Handle image uploads
            if ($request->hasFile('pipeimage1')) {
                // Delete old image if exists
                if ($pipe->pipeimage1) {
                    Storage::disk('public')->delete($pipe->pipeimage1);
                }
                $data['pipeimage1'] = $request->file('pipeimage1')->store('pipe_images', 'public');
            }

            if ($request->hasFile('pipeimage2')) {
                // Delete old image if exists
                if ($pipe->pipeimage2) {
                    Storage::disk('public')->delete($pipe->pipeimage2);
                }
                $data['pipeimage2'] = $request->file('pipeimage2')->store('pipe_images', 'public');
            }

            $pipe->update($data);

            return response()->json([
                'success' => true,
                'data' => $pipe,
                'message' => 'Pipe updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating pipe: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete the specified pipe
     */
    public function destroy($id): JsonResponse
    {
        try {
            $pipe = Pipe::find($id);

            if (!$pipe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pipe not found'
                ], 404);
            }

            $pipe->delete(); // Soft delete

            return response()->json([
                'success' => true,
                'message' => 'Pipe deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting pipe: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft deleted pipe
     */
    public function restore($id): JsonResponse
    {
        try {
            $pipe = Pipe::withTrashed()->find($id);

            if (!$pipe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pipe not found'
                ], 404);
            }

            if (!$pipe->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pipe is not deleted'
                ], 400);
            }

            $pipe->restore();

            return response()->json([
                'success' => true,
                'data' => $pipe,
                'message' => 'Pipe restored successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error restoring pipe: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete a pipe
     */
    public function forceDelete($id): JsonResponse
    {
        try {
            $pipe = Pipe::withTrashed()->find($id);

            if (!$pipe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pipe not found'
                ], 404);
            }

            // Delete associated images
            if ($pipe->pipeimage1) {
                Storage::disk('public')->delete($pipe->pipeimage1);
            }
            if ($pipe->pipeimage2) {
                Storage::disk('public')->delete($pipe->pipeimage2);
            }

            $pipe->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Pipe permanently deleted'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error permanently deleting pipe: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pipes by farmer
     */
    public function getByFarmer($farmerId): JsonResponse
    {
        try {
            $pipes = Pipe::where('farmer_id', $farmerId)
                        ->orderBy('created_at', 'desc')
                        ->get();

            return response()->json([
                'success' => true,
                'data' => $pipes,
                'message' => 'Farmer pipes retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving farmer pipes: ' . $e->getMessage()
            ], 500);
        }
    }
}
