<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * @api {post} /login User login
     * @apiName LoginUser
     * @apiGroup Users
     * @apiDescription Authenticates a user and returns an access token.  
     * Use this token as a Bearer token in the `Authorization` header for protected routes.
     *
     * @apiBody {String} email User's email address.
     * @apiBody {String} password User's password.
     *
     * @apiParamExample {json} Request-Example:
     * {
     *   "email": "admin@example.com",
     *   "password": "secret123"
     * }
     *
     * @apiSuccess {Object} user Authenticated user object with token.
     * @apiSuccess {String} user.token Access token to be used in Authorization header.
     *
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "user": {
     *     "id": 1,
     *     "name": "Admin",
     *     "email": "admin@example.com",
     *     "created_at": "2025-10-06T12:00:00.000000Z",
     *     "updated_at": "2025-10-06T12:00:00.000000Z",
     *     "token": "1|ABC123..."
     *   }
     * }
     *
     * @apiErrorExample {json} Unauthorized:
     * HTTP/1.1 401 Unauthorized
     * {
     *   "message": "Invalid email or password"
     * }
     */
    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $password ? $user->password : '')) {
            return response()->json([
                'message' => 'Invalid email or password',
            ], 401);
        }

        // Revoke old tokens
        $user->tokens()->delete();

        $user->token = $user->createToken('access')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'token' => $user->token,
            ],
        ]);
    }

    /**
     * @api {get} /users List all users
     * @apiName GetUsers
     * @apiGroup Users
     * @apiDescription Returns a list of all users. Requires authentication.
     *
     * @apiHeader {String} Authorization Bearer token.
     *
     * @apiSuccess {Object[]} users Array of user objects.
     *
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "users": [
     *     {
     *       "id": 1,
     *       "name": "Admin",
     *       "email": "admin@example.com",
     *       "created_at": "2025-10-06T12:00:00.000000Z",
     *       "updated_at": "2025-10-06T12:00:00.000000Z"
     *     }
     *   ]
     * }
     *
     * @apiErrorExample {json} Unauthorized:
     * HTTP/1.1 401 Unauthorized
     * {
     *   "message": "Unauthenticated."
     * }
     */
    public function index(Request $request)
    {
        $users = User::all();
        return response()->json([
            'users' => $users,
        ]);
    }
}
