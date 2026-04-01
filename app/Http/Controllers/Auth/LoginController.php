<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    protected function redirectTo()
    {
        // Obtener el usuario autenticado
        $user = Auth::user();
 
        // Verificar el rol del usuario y redirigir según el rol
        if ($user->hasRole('admin')) {
            return '/ruta-admin'; // Reemplaza con la ruta a la vista del administrador
           //return redirect()->route('ruta-admin', ['token' => $user->api_token]);
        } elseif ($user->hasRole('user')) {
            return '/ruta-usuario'; // Reemplaza con la ruta a la vista del usuario
        } elseif ($user->hasRole('inspector')){
            return '/ruta-inspector';
        } else {
            // Puedes agregar lógica adicional o redirigir a una ruta predeterminada si el rol no coincide
            return '/ruta-sinrol';
        }
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');

    }

    

     /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    /*public function logout(Request $request)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();
        if ($user) {
            // Calcular el tiempo de inactividad del usuario
            $inactiveTime = $user->calculateInactiveTime();
            // Actualizar el tiempo conectado del usuario
            $user->updateConnectedTime($inactiveTime);
        }
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return $this->loggedOut($request) ?: redirect('/');
    }*/
}
