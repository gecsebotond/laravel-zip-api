<?php

namespace App\Http\Controllers;

use App\Models\Place;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    /**
     * @api {get} /places Get all places
     * @apiName GetPlaces
     * @apiGroup Places
     * @apiDescription Returns a list of all places with their related county.
     *
     * @apiSuccess {Number} id Place ID.
     * @apiSuccess {String} postal_code Postal code of the place.
     * @apiSuccess {String} name Place name.
     * @apiSuccess {Number} county_id ID of the related county.
     * @apiSuccess {Object} county Related county object.
     *
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * [
     *   {
     *     "id": 1,
     *     "postal_code": "8000",
     *     "name": "Székesfehérvár",
     *     "county_id": 1,
     *     "county": {
     *       "id": 1,
     *       "name": "Fejér"
     *     }
     *   }
     * ]
     */
    public function index()
    {
        return Place::with('county')->get();
    }

    /**
     * @api {get} /places/:id Get a place by ID
     * @apiName GetPlace
     * @apiGroup Places
     * @apiDescription Returns a single place with its related county.
     *
     * @apiParam {Number} id Place ID.
     *
     * @apiSuccess {Number} id Place ID.
     * @apiSuccess {String} postal_code Postal code of the place.
     * @apiSuccess {String} name Place name.
     * @apiSuccess {Number} county_id ID of the related county.
     * @apiSuccess {Object} county Related county object.
     *
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "id": 1,
     *   "postal_code": "8000",
     *   "name": "Székesfehérvár",
     *   "county_id": 1,
     *   "county": {
     *     "id": 1,
     *     "name": "Fejér"
     *   }
     * }
     *
     * @apiErrorExample {json} Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "No query results for model [App\\Models\\Place] 99"
     * }
     */
    public function show($id)
    {
        return Place::with('county')->findOrFail($id);
    }

    /**
     * @api {post} /places Create a new place
     * @apiName CreatePlace
     * @apiGroup Places
     * @apiDescription Creates a new place. Requires authentication.
     *
     * @apiHeader {String} Authorization Bearer token.
     *
     * @apiBody {String} postal_code Postal code of the place.
     * @apiBody {String} name Name of the place.
     * @apiBody {Number} county_id ID of the related county.
     *
     * @apiParamExample {json} Request-Example:
     * {
     *   "postal_code": "8000",
     *   "name": "Székesfehérvár",
     *   "county_id": 1
     * }
     *
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 201 Created
     * {
     *   "id": 21,
     *   "postal_code": "8000",
     *   "name": "Székesfehérvár",
     *   "county_id": 1,
     *   "updated_at": "2025-10-06T12:00:00.000000Z",
     *   "created_at": "2025-10-06T12:00:00.000000Z"
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
            'postal_code' => 'required|string',
            'name'        => 'required|string',
            'county_id'   => 'required|exists:counties,id',
        ]);

        $place = Place::create($request->all());

        return response()->json($place, 201);
    }

    /**
     * @api {put} /places/:id Update a place
     * @apiName UpdatePlace
     * @apiGroup Places
     * @apiDescription Updates an existing place by ID. Requires authentication.
     *
     * @apiHeader {String} Authorization Bearer token.
     *
     * @apiParam {Number} id Place ID.
     *
     * @apiBody {String} [postal_code] Postal code of the place.
     * @apiBody {String} [name] Name of the place.
     * @apiBody {Number} [county_id] ID of the related county.
     *
     * @apiParamExample {json} Request-Example:
     * {
     *   "name": "Updated Name"
     * }
     *
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "id": 1,
     *   "postal_code": "8000",
     *   "name": "Updated Name",
     *   "county_id": 1,
     *   "updated_at": "2025-10-06T12:30:00.000000Z",
     *   "created_at": "2025-10-06T12:00:00.000000Z"
     * }
     */
    public function update(Request $request, $id)
    {
        $place = Place::findOrFail($id);

        $request->validate([
            'postal_code' => 'sometimes|required|string',
            'name'        => 'sometimes|required|string',
            'county_id'   => 'sometimes|required|exists:counties,id',
        ]);

        $place->update($request->all());

        return response()->json($place);
    }

    /**
     * @api {delete} /places/:id Delete a place
     * @apiName DeletePlace
     * @apiGroup Places
     * @apiDescription Deletes a place by ID. Requires authentication.
     *
     * @apiHeader {String} Authorization Bearer token.
     *
     * @apiParam {Number} id Place ID.
     *
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 No Content
     */
    public function destroy($id)
    {
        $place = Place::findOrFail($id);
        $place->delete();

        return response()->json(null, 204);
    }
}
