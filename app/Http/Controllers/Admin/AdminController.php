<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function adminView()
    {
        //$users = User::all();
        $user = Auth::user();
        $token = $user->api_token;
        $users = User::paginate(10);
        
        return view('admin', compact('users', 'token'));
    }
}
