<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    /**
 * @OA\Post(
 *     path="/api/forgot-password",
 *     summary="Forgot password",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email"},
 *             @OA\Property(property="email", type="string", format="email", example="john@gmail.com")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Password reset link sent successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="responseCode", type="integer", example=200),
 *             @OA\Property(property="responseStatus", type="string", example="success"),
 *             @OA\Property(property="responseMessage", type="string", example="Lien envoyé!")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation failed",
 *         @OA\JsonContent(
 *             @OA\Property(property="responseCode", type="integer", example=400),
 *             @OA\Property(property="responseStatus", type="string", example="error"),
 *             @OA\Property(property="responseMessage", type="string", example="Lien non envoyé!"),
 *             @OA\Property(property="data", type="string", example="Please wait before retrying.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="responseCode", type="integer", example=422),
 *             @OA\Property(property="responseStatus", type="string", example="error"),
 *             @OA\Property(property="responseMessage", type="string", example="Veuillez vérifier l'adresse e-mail!"),
 *             @OA\Property(property="data", type="object", example={"email": {"The email field is required."}})
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="An error occurred",
 *         @OA\JsonContent(
 *             @OA\Property(property="responseCode", type="integer", example=500),
 *             @OA\Property(property="responseStatus", type="string", example="error"),
 *             @OA\Property(property="responseMessage", type="string", example="Une erreur est survenue!"),
 *             @OA\Property(property="data", type="object", example={"email": {"The email field is required."}})
 *         )
 *     )
 * )
 */
    public function forgotPassword(ForgotPasswordRequest $request) {
        try{
            $request->validated();

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            $responseCode = 200;
            $responseStatus = "success";
            $responseMessage = "Lien envoyé!";
        } else {
            $responseCode = 400;
            $responseStatus = "error";
            $responseMessage = "Lien non envoyé!";
            $data =  __($status);
        }
        LogHelper::logRequest($request, $responseCode, $responseStatus, $responseMessage);

            return ResponseHelper::responseAPI($responseCode, $responseStatus, $responseMessage, $data);
        
    } catch (ValidationException $e) {
        // Handle validation errors
        $responseCode = 422;
        $responseStatus = "error";
        $responseMessage = "Veuillez vérifier l'adresse e-mail!";
        $data = $e->errors();
        
        LogHelper::logRequest($request, $responseCode, $responseStatus, $responseMessage);

        return ResponseHelper::responseAPI($responseCode, $responseStatus, $responseMessage, $data);
    } catch (\Exception $e) {
        // Handle other exceptions
        $responseCode = 500;
        $responseStatus = "error";
        $responseMessage = "Une erreur est survenue!";
        $data = $e->getMessage();

        LogHelper::logRequest($request, $responseCode, $responseStatus, $responseMessage);

        return ResponseHelper::responseAPI($responseCode, $responseStatus, $responseMessage, $data);
    }
}

/**
 * @OA\Post(
 *     path="/api/reset-password",
 *     summary="Reset password",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"token", "email", "password", "password_confirmation"},
 *             @OA\Property(property="token", type="string", example="token_here"),
 *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="new_password"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="new_password_confirmation")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Password reset successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="responseCode", type="integer", example=200),
 *             @OA\Property(property="responseStatus", type="string", example="success"),
 *             @OA\Property(property="responseMessage", type="string", example="Mot de passe réinitialisé avec succès!")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation failed",
 *         @OA\JsonContent(
 *             @OA\Property(property="responseCode", type="integer", example=400),
 *             @OA\Property(property="responseStatus", type="string", example="error"),
 *             @OA\Property(property="responseMessage", type="string", example="Veuillez vérifier le mot de passe!"),
 *             @OA\Property(property="data", type="object", example={"email": {"The password field is required."}})
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation failed",
 *         @OA\JsonContent(
 *             @OA\Property(property="responseCode", type="integer", example=422),
 *             @OA\Property(property="responseStatus", type="string", example="error"),
 *             @OA\Property(property="responseMessage", type="string", example="Veuillez vérifier le mot de passe!"),
 *             @OA\Property(property="data", type="object", example= {"password": {"The password confirmation does not match."}})
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="An error occurred",
 *         @OA\JsonContent(
 *             @OA\Property(property="responseCode", type="integer", example=500),
 *             @OA\Property(property="responseStatus", type="string", example="error"),
 *             @OA\Property(property="responseMessage", type="string", example="Une erreur est survenue!"),
 *             @OA\Property(property="data", type="object", example={"email": {"The password field is required."}})
 *         )
 *     )
 * )
 */

public function resetPassword(ResetPasswordRequest $request) {
    try {
         $request->validated();
        
        // Attempt to reset the password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password)
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        // Handle the password reset status
        if ($status == Password::PASSWORD_RESET) {
            $responseCode = 200;
            $responseStatus = "success";
            $responseMessage = "Mot de passe réinitialisé avec succès!";
        } else {
            $responseCode = 400;
            $responseStatus = "error";
            $responseMessage = "Mot de passe non réinitialisé";
            $data = __($status);
        }
        LogHelper::logRequest($request, $responseCode, $responseStatus, $responseMessage);

        return ResponseHelper::responseAPI($responseCode, $responseStatus, $responseMessage, $data);

    } catch (ValidationException $e) {
        // Handle validation errors
        $responseCode = 422;
        $responseStatus = "error";
        $responseMessage = "Veuillez vérifier les champs!";
        $data = $e->errors();
        
        LogHelper::logRequest($request, $responseCode, $responseStatus, $responseMessage);

        return ResponseHelper::responseAPI($responseCode, $responseStatus, $responseMessage, $data);

    } catch (\Exception $e) {
        // Handle other exceptions
        $responseCode = 400;
        $responseStatus = "error";
        $responseMessage = "Une erreur est survenue!";
        $data = $e->getMessage();

        LogHelper::logRequest($request, $responseCode, $responseStatus, $responseMessage);

        return ResponseHelper::responseAPI($responseCode, $responseStatus, $responseMessage, $data);
    }
}

}
