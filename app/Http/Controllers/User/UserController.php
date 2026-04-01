<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function userView()
    {
        // Lógica específica para la vista de usuario
        //$this->authorize('manageMapa', auth()->user());
        //return view('vistaUser');
        $user = Auth::user();
        $token = $user->api_token;
        $users = User::paginate(10);
        return view('vistaUser', compact('users', 'token'));
    }
}
