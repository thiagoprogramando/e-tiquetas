<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppController extends Controller {
    
    public function index (Request $request) {

        $user = Auth::user();

        $stats = [
            'templates' => $user->layouts()->count(),
            'etiquetas' => $user->labels()->count(),
            'usuarios'  => $user->affiliates()->count(),
            'remessas'  => $user->datas()->count()
        ];

        return view('app.app', [
            'stats' => $stats
        ]);
    }
}
