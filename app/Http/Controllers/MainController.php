<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MainController extends Controller
{
    public function home(): View
    {
        return view('home');
    }

    public function generateExercises(Request $request)
    {
        echo "gerar exercicios";
    }

    public function printExercises()
    {
        echo "imprimir exercicios";
    }

    public function exportExercises()
    {
        echo "exportat exercicios para .txt";
    }
}
