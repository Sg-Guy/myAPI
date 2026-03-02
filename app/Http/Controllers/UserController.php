<?php

namespace App\Http\Controllers;

use App\Models\User;
use Dotenv\Validator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use League\Config\Exception\ValidationException;

class UserController extends Controller
{
    public function login (Request $request) {

    $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ] , [
        'email.required'=>"Veuillez entrez votre email" ,
        'email.email'=>"Veuillez saisir un mail valide !" ,
        'password.required'=>"Veuillez entrez un mot de passe" ,
    ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
            'message' => 'Identifiants invalides !',
            ] ,);
        }

        // Supprime les anciens tokens (optionnel mais recommandé)
        $user->tokens()->delete();

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ],200);
   
    }
   

   public function register (Request $request){

       $request->validate([
           'name'=>"required",
           'email'=>"required|email|unique:users,email",
           //'role_id'=>"required|exists:roles,id",
           'password'=>"required|min:6",

    ] , [
        'name.required'=>"Veuillez entrez votre nom" ,
        'email.required'=>"Veuillez entrez votre email" ,
        'email.email'=>"Veuillez saisir un mail valide" ,
        'email.email'=>"Veuillez saisir un mail valide" ,
        'role_id.unique'=>"Cet utilisateur existe déjà !" ,
       // 'role_id.exists'=>"Ce rôle n'existe pas" ,
        'password.required'=>"Veuillez choisir un mot de passe" ,
        'password.min'=>"Le mot de passe doit contenir au moins 6 caractères" ,
    ]);

    $user = new User();
    $user->name = $request->name ;
    $user->email = $request->email ;
    $user->role_id = $request->role_id ?? 1;
    $user->password = $request->password ;
//dd($user);
    try {
        $user->save();
         $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message'=>"Votre compte a bien été créé !" ,
            'user' => $user,
            'token' => $token, ]) ;
    } catch (Exception $e) {
        return  response()->json([
            'message' => "Une erreur est survenue lors de la création du compte".$e ,
        ], 500);
    }

    }


    public function profil(Request $request)
    {
        if ($request->user()) {

            return response()->json([ 'user'=>$request->user()] ,200);
        } else {
            return response()->json(["message"=>"Veuillez vous connecter"] , 400);
        }
    }

    // 🔹 DÉCONNEXION
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnecté avec succès',
        ]);
    }


/*public function update(Request $request)
    {
        //dd($request->user() ) ;
        // 1. Validation (Laravel 12 gère automatiquement la réponse d'erreur en API)
        $request->validate([
            'name'  => 'required',
            'email' => 
                'required|email', 
                  
               // Rule::unique('users')->ignore($request->user()->email)
            
        ]);

        dd ($request->validated) ;
        // 2. Mise à jour de l'utilisateur authentifié
        $request->user()->update($validated);

        // 3. Réponse
        return response()->json([
            'status'  => 'success',
            'message' => 'Profil mis à jour avec succès',
            'user'    => $request->user(),
        ]);
    }*/

    
}
