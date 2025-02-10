<?php

namespace App\Http\Controllers;

use App\Models\SubServiece;
use App\Http\Requests\StoreSubServieceRequest;
use App\Http\Requests\UpdateSubServieceRequest;
use App\Models\Service;
use Illuminate\Http\Request;

class SubServieceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = SubServiece::get();

        return response()->json($query);
    }

    public function getSubServiecesAndParents()
    {
        $query = SubServiece::with('service')->get();

        return response()->json($query);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSubServieceRequest $request)
    {
        $assign = $request->validate(
            [
                'service_id' => 'required|exists:services,id',
                'sub_service_name' => 'required|string',
            ]
        );
        try {
            $assign = SubServiece::create($assign);
            return response()->json($assign);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SubServiece $subService)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubServiece $subService)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Find the sub-service by its ID
        $sub = SubServiece::findOrFail($id);
        // Get individual inputs from the request
        $serviceId = $request->input('service_id');
        $subServiceName = $request->input('sub_service_name');
        try {
            // Update the sub-service with the provided data
            $sub->update([
                'service_id' => $serviceId,
                'sub_service_name' => $subServiceName,
            ]);

            return response()->json([
                'message' => 'SubService updated successfully',
                'data' => $sub,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the SubService',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $sub = SubServiece::findOrFail($id);
            $sub->delete();
            return response()->json(['message' => 'Sub Serviece deleted successfully']);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }
}
