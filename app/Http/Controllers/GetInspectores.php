<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inspector;
class GetInspectores extends Controller
{
    public function getInspectores(Request $request )
    {
        $inspectores = Inspector::all();
        $respuesta = response()->json(array('msg'=> $inspectores), 200);
        return $respuesta;
    }

}
