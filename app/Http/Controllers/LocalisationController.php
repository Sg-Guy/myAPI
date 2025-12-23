<?php

namespace App\Http\Controllers;

use App\Models\Localisation;
use Illuminate\Http\Request;

class LocalisationController extends Controller
{

    public function index (Request $request) {
        $localisation = Localisation::where ('user_id' , $request->user()->id)->get() ;
        //dd ($localisation) ;
        return response()->json(
            [
                'localisations'=>$localisation ,
            ] , 200
        ) ;
    }
    public function store (Request $request) {
        $request->validate(
            [
                "pays" => "required|string" , 
                "ville" => "required|string" , 
                "rue" => "required|string" , 
                "code_postal" => "required|string"
            ]
        ) ;

        $localiastion  = new Localisation() ;
        $localiastion->user_id = $request->user()->id ;
        $localiastion->pays = $request->pays ;
        $localiastion->ville = $request->ville ;
        $localiastion->rue = $request->rue ;
        $localiastion->code_postal = $request->code_postal ;

        $localiastion->save();
        return response()->json(
            [
                'localisation' => $localiastion ,
            ] ,201
        ) ;
    }
    public function update (Request $request , ) {
        $request->validate(
            [
                "pays" => "required|string" , 
                "ville" => "required|string" , 
                "rue" => "required|string" , 
                "code_postal" => "required|string"
            ]
        ) ;

        $localiastion  = new Localisation() ;
        $localiastion->user_id = $request->user()->id ;
        $localiastion->pays = $request->pays ;
        $localiastion->ville = $request->ville ;
        $localiastion->rue = $request->rue ;
        $localiastion->code_postal = $request->code_postal ;

        $localiastion->save();
        return response()->json(
            [
                'localisation' => $localiastion ,
            ] ,201
        ) ;
    }


}
