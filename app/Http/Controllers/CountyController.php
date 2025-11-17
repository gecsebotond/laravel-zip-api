<?php

namespace App\Http\Controllers;

use App\Models\County;
use Illuminate\Http\Request;

class CountyController extends Controller
{
    /**
     * @api {get} /counties Get all counties
     * @apiName GetCounties
     * @apiGroup Counties
     * @apiDescription Returns a list of all counties with their related places.
     *
     * @apiSuccess {String} status Response status ("success").
     * @apiSuccess {Object[]} data Array of county objects.
     * @apiSuccess {Number} data.id County ID.
     * @apiSuccess {String} data.name County name.
     * @apiSuccess {Object[]} data.places Related places for this county.
     *
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Fejér",
     *       "places": [
     *         { "id": 10, "postal_code": "8000", "name": "Székesfehérvár", "county_id": 1 }
     *       ]
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $counties = County::all();
    
        return response()->json([
            'status' => 'success',
            'data' => $counties
        ]);
    }
    

    /**
     * @api {get} /counties/:id Get a county by ID
     * @apiName GetCounty
     * @apiGroup Counties
     * @apiDescription Returns a single county with its places.
     *
     * @apiParam {Number} id County's unique ID.
     *
     * @apiSuccess {String} status Response status ("success").
     * @apiSuccess {Object} data County object.
     *
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "status": "success",
     *   "data": {
     *     "id": 1,
     *     "name": "Fejér",
     *     "places": [
     *       { "id": 10, "postal_code": "8000", "name": "Székesfehérvár", "county_id": 1 }
     *     ]
     *   }
     * }
     *
     * @apiErrorExample {json} Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "No query results for model [App\\Models\\County] 99"
     * }
     */
    public function show($id)
    {
        $county = County::with('places')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $county
        ]);
    }

    /**
     * @api {post} /counties Create a new county
     * @apiName CreateCounty
     * @apiGroup Counties
     * @apiDescription Creates a new county. Requires authentication.
     *
     * @apiHeader {String} Authorization Bearer token.
     *
     * @apiBody {String} name County name.
     *
     * @apiParamExample {json} Request-Example:
     * {
     *   "name": "Fejér"
     * }
     *
     * @apiSuccess {String} status Response status ("success").
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Object} data Created county object.
     *
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 201 Created
     * {
     *   "status": "success",
     *   "message": "County created successfully",
     *   "data": {
     *     "id": 21,
     *     "name": "Fejér",
     *     "created_at": "2025-10-06T12:34:56.000000Z",
     *     "updated_at": "2025-10-06T12:34:56.000000Z"
     *   }
     * }
     *
     * @apiErrorExample {json} Validation Error:
     * HTTP/1.1 422 Unprocessable Entity
     * {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["The name field is required."]
     *   }
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:counties,name',
        ]);

        $county = County::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'County created successfully',
            'data' => $county
        ], 201);
    }

    /**
     * @api {put} /counties/:id Update a county
     * @apiName UpdateCounty
     * @apiGroup Counties
     * @apiDescription Updates an existing county by ID. Requires authentication.
     *
     * @apiHeader {String} Authorization Bearer token.
     *
     * @apiParam {Number} id County ID.
     * @apiBody {String} name County name.
     *
     * @apiParamExample {json} Request-Example:
     * {
     *   "name": "Heves"
     * }
     *
     * @apiSuccess {String} status Response status ("success").
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Object} data Updated county object.
     *
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "status": "success",
     *   "message": "County updated successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Heves",
     *     "created_at": "2025-10-06T12:34:56.000000Z",
     *     "updated_at": "2025-10-06T12:40:00.000000Z"
     *   }
     * }
     */
    public function update(Request $request, $id)
    {
        $county = County::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:counties,name,' . $county->id,
        ]);

        $county->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'County updated successfully',
            'data' => $county
        ]);
    }

    /**
     * @api {delete} /counties/:id Delete a county
     * @apiName DeleteCounty
     * @apiGroup Counties
     * @apiDescription Deletes a county by ID. Requires authentication.
     *
     * @apiHeader {String} Authorization Bearer token.
     *
     * @apiParam {Number} id County ID.
     *
     * @apiSuccess {String} status Response status ("success").
     * @apiSuccess {String} message Success message.
     *
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 No Content
     * {
     *   "status": "success",
     *   "message": "County deleted successfully"
     * }
     *
     * @apiErrorExample {json} Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "No query results for model [App\\Models\\County] 99"
     * }
     */
    public function destroy($id)
    {
        $county = County::findOrFail($id);
        $county->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'County deleted successfully'
        ], 204);
    }
}
