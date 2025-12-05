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
        //form validation
        $request->validate([
            'check_sum' => 'required_without_all:check_subtraction,check_multiplication,check_division',
            'check_subtraction' => 'required_without_all:check_sum,check_multiplication,check_division',
            'check_multiplication' => 'required_without_all:check_sum,check_subtraction,check_division',
            'check_division' => 'required_without_all:check_sum,check_subtraction,check_multiplication',
            'number_one' => 'required|integer|min:0|max:999|lt:number_two',
            'number_two' => 'required|integer|min:0|max:999',
            'number_exercises' => 'required|integer|min:5|max:100',
        ]);

        //get selected data
        $operations = [];
        if($request->check_sum) {
            $operations[] = 'sum';
        }
        if($request->check_subtraction) {
            $operations[] = 'subtraction';
        }
        if($request->check_multiplication) {
            $operations[] = 'multiplication';
        }
        if($request->check_division) {
            $operations[] = 'division';
        }

        //get numbers(min and max)
        $min = $request->number_one;
        $max = $request->number_two;

        $numberExercises = $request->number_exercises;

        //generate exercises
        $exercises = [];
        for($index = 1 ; $index <= $numberExercises; $index++) {
            $exercises[] = $this->genExercise($index, $operations, $min, $max);

        }

        // place exercises in session
        $request->session()->put('exercises', $exercises);
        // ou
        //session(['exercises' => $exercises]);


        return view('operations', ['exercises' => $exercises]);
    }

    public function printExercises()
    {
        if(!session()->has('exercises')) {
            return redirect()->route('home');
        }
        $exercises = session('exercises');

        echo '<pre>';
        echo '<h1>Exercicios de Matematica ('.env('APP_NAME').')</h1>';
        echo '<hr>';
        foreach($exercises as $exercise) {
            echo '<h2> <small> '.str_pad($exercise['exercise_number'], 2, '0', STR_PAD_LEFT) .' -> </small> '.$exercise['exercise'].' </h2>';

        }

        echo '<hr>';
        echo '<h3>Solucões</h3>';
        foreach($exercises as $exercise) {
            echo '<h4> <small> '.str_pad($exercise['exercise_number'], 2, '0', STR_PAD_LEFT) .' -> </small> '.$exercise['solution'].' </h4>';

        }
    }


    public function exportExercises()
    {
        if(!session()->has('exercises')) {
            return redirect()->route('home');
        }
        $exercises = session('exercises');

        $filename = 'exercicios_matematica_'.date('Ymd_His').'.txt';
        $file = fopen($filename, 'w');

        fwrite($file, "Exercicios de Matematica (".env('APP_NAME').")\n");
        fwrite($file, "=============================\n\n");

        foreach($exercises as $exercise) {
            fwrite($file, str_pad($exercise['exercise_number'], 2, '0', STR_PAD_LEFT) .' -> '.$exercise['exercise']."\n");
        }

        fwrite($file, "\n=============================\n");
        fwrite($file, "Solucões\n\n");

        foreach($exercises as $exercise) {
            fwrite($file, str_pad($exercise['exercise_number'], 2, '0', STR_PAD_LEFT) .' -> '.$exercise['solution']."\n");
        }

        fclose($file);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    private function genExercise($index, $operations, $min, $max): array
    {
            $operation = $operations[array_rand(array_filter($operations))];
            $number1 = rand($min, $max);
            $number2 = rand($min, $max);

            $exercise= '';
            $solution = '';

            switch($operation) {
                case 'sum':
                    $exercise = "$number1 + $number2  = ";
                    $solution = $number1 + $number2;
                    break;
                case 'subtraction':
                    $exercise = "$number1 - $number2 = ";
                    $solution = $number1 - $number2 ;
                    break;
                case 'multiplication':
                    $exercise = "$number1 x $number2 = ";
                    $solution = $number1 * $number2;
                    break;
                case 'division':
                    //avoid division by zero
                    if($number2 == 0) {
                        $number2 = 1;
                    }
                    $exercise = "$number1 : $number2 = ";
                    $solution = round($number1 / $number2, 2);
                    break;
            }

            //if solution is float, format to 2 decimal places
            if(is_float($solution)) {
                $solution = number_format($solution, 2);
            }

            return [
                'operation' => $operation,
                'exercise_number' => $index,
                'exercise' => $exercise,
                'solution' => "$exercise $solution"
            ];


    }
}
