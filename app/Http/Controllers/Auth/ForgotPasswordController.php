<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        $email = trim((string) $request->input('email'));
        $user = User::query()
            ->where('email', $email)
            ->first();

        if (!$user) {
            return $this->sendResetLinkFailedResponse($request, Password::INVALID_USER);
        }

        $token = $this->broker()->createToken($user);
        $user->sendPasswordResetNotification($token);

        return $this->sendResetLinkResponse($request, Password::RESET_LINK_SENT);
    }
}
