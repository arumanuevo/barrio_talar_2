<?php

use Illuminate\Support\Facades\Mail;
use App\Mail\CorreoEjemplo; // Importa la clase de correo que deseas enviar

class EmailController extends Controller
{
    public function enviarCorreo()
    {
        // Lógica para enviar el correo electrónico
        $user = auth()->user(); // Obtén el usuario autenticado

        // Envía el correo electrónico utilizando la clase de correo que has creado
        Mail::to($user->email)->send(new CorreoEjemplo());

        return redirect()->back()->with('success', 'Correo electrónico enviado correctamente.');
    }
}

