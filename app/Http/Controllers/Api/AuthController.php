<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Info(
 *     title="Catch Me API",
 *     version="1.0.0",
 *     description="Social proximity app API for connecting nearby people"
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     securityScheme="bearerAuth",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Register a new user with email or phone",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com", description="Email (required if phone not provided)"),
     *             @OA\Property(property="phone", type="string", example="+33612345678", description="Phone (required if email not provided)"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="gender", type="string", enum={"male","female","other"}, description="Optional, defaults to 'male'"),
     *             @OA\Property(property="date_of_birth", type="string", format="date", example="1995-01-01"),
     *             @OA\Property(property="language", type="string", enum={"fr","ar"}, description="Optional, defaults to 'fr'")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User registered successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function register(Request $request)
    {
        // Accept either email or phone (at least one is required)
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required_without:phone|nullable|email|unique:users,email',
            'phone' => 'required_without:email|nullable|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'nullable|same:password',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'language' => 'nullable|in:fr,ar',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Generate unique phone if only email provided
        $phone = $request->phone;
        if (!$phone && $request->email) {
            $phone = 'email_' . md5($request->email);
        }

        // Default gender if not provided
        $gender = $request->gender ?? 'male';

        $user = User::create([
            'name' => $request->name,
            'phone' => $phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender' => $gender,
            'date_of_birth' => $request->date_of_birth,
            'language' => $request->language ?? 'fr',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => __('auth.registered'),
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Login user with email or phone",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"password"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="phone", type="string", example="+33612345678"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="fcm_token", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful"),
     *     @OA\Response(response=401, description="Wrong password"),
     *     @OA\Response(response=404, description="Email or phone not found")
     * )
     */
    public function login(Request $request)
    {
        // Accept both phone and email for login
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:phone|string',
            'phone' => 'required_without:email|string',
            'password' => 'required|string',
            'fcm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find user by email or phone
        $user = null;
        $loginField = null;

        if ($request->email) {
            $user = User::where('email', $request->email)->first();
            $loginField = 'email';

            // Check if email exists
            if (!$user) {
                return response()->json([
                    'message' => 'Email not found',
                    'field' => 'email'
                ], 404);
            }
        } elseif ($request->phone) {
            $user = User::where('phone', $request->phone)->first();
            $loginField = 'phone';

            // Check if phone exists
            if (!$user) {
                return response()->json([
                    'message' => 'Phone number not found',
                    'field' => 'phone'
                ], 404);
            }
        }

        // Check password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Wrong password',
                'field' => 'password'
            ], 401);
        }

        if ($user->is_banned) {
            return response()->json(['message' => __('auth.banned')], 403);
        }

        // Update FCM token if provided
        if ($request->fcm_token) {
            $user->update(['fcm_token' => $request->fcm_token]);
        }

        // Update last active time
        $user->update(['last_active_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => __('auth.login_success'),
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Logout user",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Logout successful")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => __('auth.logout_success')]);
    }

    /**
     * @OA\Get(
     *     path="/api/auth/user",
     *     summary="Get authenticated user",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="User data retrieved")
     * )
     */
    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/social",
     *     summary="Social authentication (Google/Apple)",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"provider","provider_id","name"},
     *             @OA\Property(property="provider", type="string", enum={"google","apple"}),
     *             @OA\Property(property="provider_id", type="string"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="gender", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful")
     * )
     */
    public function socialAuth(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required|in:google,apple',
            'provider_id' => 'required|string',
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'gender' => 'nullable|in:male,female,other',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $providerField = $request->provider . '_id';

        $user = User::where($providerField, $request->provider_id)->first();

        if (!$user) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone ?? 'social_' . $request->provider_id,
                'password' => Hash::make(uniqid()),
                'gender' => $request->gender ?? 'other',
                $providerField => $request->provider_id,
            ]);
        }

        if ($user->is_banned) {
            return response()->json(['message' => __('auth.banned')], 403);
        }

        $user->update(['last_active_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => __('auth.login_success'),
            'user' => $user,
            'token' => $token,
        ]);
    }
}
