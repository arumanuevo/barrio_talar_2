<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use App\Models\User; // Asegúrate de usar el modelo correcto
use App\Notifications\CustomResetPasswordNotification;
use App\Providers\RouteServiceProvider;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');
    }

    /*protected function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = app('auth.password.broker')->createToken($user);
            $user->notify(new CustomResetPasswordNotification($token));
            return back()->with('status', trans('passwords.sent'));
        }

        return back()->withErrors(['email' => trans('passwords.user')]);
    }*/

    protected function sendResetLinkEmail(Request $request, $response)
{
    $user = User::where('email', $request->email)->first();

    // Check if the user exists before sending the notification
    if ($user) {
        $user->notify(new ResetPasswordNotification($response));
    }

    return $this->sendResetLinkResponse($response);
}

}
