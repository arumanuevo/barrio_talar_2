<?php

namespace App\Http\Controllers\SinRol;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SinRolController extends Controller
{
    public function sinRolView()
    {
       
        //$this->authorize('manageMapa', auth()->user());
        //return view('userSinRol');
        return view('userSinRol');
    }
}
