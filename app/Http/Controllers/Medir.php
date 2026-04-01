<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Medir extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function medir(Request $request)
    {
        $user = Auth::user();
        $token = $user;

        // Obtener los lotes y ordenarlos de manera natural
        $lotes = User::where('medidor', '<>', 'N/A')
            ->get()
            ->sortBy(function($model) {
                // Extraer el número del lote (incluso si tiene sufijos como "bis")
                preg_match('/(\d+)/', $model->lote, $matches);
                return $matches ? (int)$matches[0] : 0;
            });

        return view('medir')->with(compact('lotes', 'token'));
    }
}
