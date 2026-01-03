<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /**
     * Reset the user's password.
     */
    public function reset(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password_hash' => Hash::make($password),
                    'must_change_password' => false,
                ])->save();

                // Revoke all existing tokens for security
                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return ApiResponse::success(
                null,
                'Your password has been reset successfully. Please login with your new password.'
            );
        }

        // Handle different failure statuses
        $message = match ($status) {
            Password::INVALID_USER => 'No account found with this email address.',
            Password::INVALID_TOKEN => 'This password reset link is invalid or has expired.',
            Password::RESET_THROTTLED => 'Please wait before attempting another reset.',
            default => 'Unable to reset password. Please try again.',
        };

        return ApiResponse::error($message, 400);
    }
}
