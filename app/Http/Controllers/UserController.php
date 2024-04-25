<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\LogHelper;
use App\Helpers\ResponseHelper;
use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Http\Response;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use App\Models\PasswordReset;


class UserController extends Controller
{

    /**
 * @OA\Post(
 *     path="/api/register",
 *     summary="Register a new user",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"first_name", "last_name", "gender", "birth_date", "phone_number", "email", "password"},
 *             @OA\Property(property="first_name", type="string", example="John"),
 *             @OA\Property(property="last_name", type="string", example="Doe"),
 *             @OA\Property(property="gender", type="string", example="male"),
 *             @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01"),
 *             @OA\Property(property="phone_number", type="string", example="123456789"),
 *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="responseCode", type="integer", example=200),
 *             @OA\Property(property="responseStatus", type="string", example="success"),
 *             @OA\Property(property="responseMessage", type="string", example="Utilisateur ajouté!"),
 *             @OA\Property(property="data", type="object", ref="#/components/schemas/User")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="responseCode", type="integer", example=422),
 *             @OA\Property(property="responseStatus", type="integer", example="error"),
 *             @OA\Property(property="responseMessage", type="string", example="Veuillez vérifier les champs!"),
 *             @OA\Property(property="data", type="object", example="null")
 *         )
 *     )
 * )
 */
    public function register(RegisterRequest $request)
    {
        $user = null;
        if ($request->validated()) {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'gender' => $request->gender,
                'birth_date' => $request->birth_date,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);
            $responseCode = 200;
            $responseStatus = 'success';
            $responseMessage = 'Utilisateur ajouté!';
        } else {
            $responseCode = 422;
            $responseStatus = 'error';
            $responseMessage = 'Veuillez vérifier les champs!';
        }

        LogHelper::logRequest($request, $responseCode, $responseStatus, $responseMessage);

        return ResponseHelper::responseAPI($responseCode, $responseStatus, $responseMessage, $user);
    }

    /**
 * @OA\Post(
 *     path="/api/login",
 *     summary="User login",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email", "password"},
 *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful login",
 *         @OA\JsonContent(
 *             @OA\Property(property="responseCode", type="integer", example=200),
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Utilisateur connecté!"),
 *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Invalid email",
 *         @OA\JsonContent(
 *             @OA\Property(property="responseCode", type="integer", example=404),
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Adresse mail invalide!"),
 *             @OA\Property(property="token", type="string", example="null")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Invalid password or validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="responseCode", type="integer", example=422),
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Le mot de passe est incorrect!"),
 *             @OA\Property(property="token", type="string", example="null")
 *         )
 *     )
 * )
 */
    public function login(LoginRequest $request) {

        $token = null;

        if ($request->validated()) {

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                $responseCode = 404;
                $responseStatus = "error";
                $responseMessage = 'Adresse mail invalide!';
            } else if (!Hash::check($request->password, $user->password)) {
                $responseCode = 422;
                $responseStatus = "error";
                $responseMessage = 'Le mot de passe est incorrect!';
            } else {
                $token = $user->createToken('UserToken')->plainTextToken;
                $responseCode = 200;
                $responseStatus = "success";
                $responseMessage = 'Utilisateur connecté!';
            }
        } else {
            $responseCode = 422;
            $responseStatus = "error";
            $responseMessage = "Veuillez vérifier l'adresse mail et le mot de passe!";
        }

        LogHelper::logRequest($request, $responseCode, $responseStatus, $responseMessage);

        return ResponseHelper::responseAPI($responseCode, $responseStatus, $responseMessage, $token);
    }

    /**
 * @OA\Post(
 *     path="/api/logout",
 *     summary="User logout",
 *     tags={"Authentication"},
 *     @OA\Response(
 *         response=200,
 *         description="Successful logout",
 *         @OA\JsonContent(
 *             @OA\Property(property="responseCode", type="integer", example=200),
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Utilisateur déconnecté!")
 *         )
 *     )
 * )
 */
    public function logout() {
        Auth::user()->tokens()->delete();
        $responseCode = 200;
        $responseStatus = "success";
        $responseMessage = "Utilisateur déconnecté!";

        LogHelper::logRequest(request(), $responseCode, $responseStatus, $responseMessage);

        return ResponseHelper::responseAPI($responseCode, $responseStatus, $responseMessage, null);
    }
}
