<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Send a password reset link to the given user.
     */
    public function sendResetLink(ForgotPasswordRequest $request)
    {
        // Send password reset link
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return ApiResponse::success(
                null,
                'Password reset link has been sent to your email address.'
            );
        }

        // Handle different failure statuses
        $message = match ($status) {
            Password::INVALID_USER => 'No account found with this email address.',
            Password::RESET_THROTTLED => 'Please wait before requesting another reset link.',
            default => 'Unable to send password reset link. Please try again.',
        };

        return ApiResponse::error($message, 400);
    }
}
