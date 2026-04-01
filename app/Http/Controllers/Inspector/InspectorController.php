<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InspectorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function inspectorView()
    {
        // Lógica específica para la vista de usuario
        //$this->authorize('manageMapa', auth()->user());
        return view('vistaInspector');
    }
}
