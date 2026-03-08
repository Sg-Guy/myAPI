<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Users",
    description: "Gestion des utilisateurs"
)]
class UserController extends Controller
{
    #[OA\Post(
        path: "/api/user/login",
        tags: ["Users"],
        summary: "Connexion d'un utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["email", "password"],
                    properties: [
                        new OA\Property(property: "email", type: "string", example: "user@mail.com"),
                        new OA\Property(property: "password", type: "string", example: "password123"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Connexion réussie",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "user", type: "object"),
                        new OA\Property(property: "token", type: "string")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Identifiants invalides"),
            new OA\Response(response: 403, description: "Accès interdit")
        ]
    )]
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with('role')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Identifiants invalides !'], 401);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    #[OA\Post(
        path: "/api/user/register",
        tags: ["Users"],
        summary: "Création d'un utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["name", "email", "password"],
                    properties: [
                        new OA\Property(property: "name", type: "string", example: "John Doe"),
                        new OA\Property(property: "email", type: "string", example: "user@mail.com"),
                        new OA\Property(property: "password", type: "string", example: "password123"),
                        new OA\Property(property: "role_id", type: "integer", example: 1)
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Utilisateur créé",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(property: "user", type: "object"),
                        new OA\Property(property: "token", type: "string")
                    ]
                )
            ),
            new OA\Response(response: 500, description: "Erreur serveur")
        ]
    )]
    public function register(Request $request)
    {
        $request->validate([
            'name' => "required|string|max:255",
            'email' => "required|email|unique:users,email",
            'password' => "required|min:6",
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role_id' => $request->role_id ?? 1,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => "Votre compte a bien été créé !",
                'user' => $user,
                'token' => $token,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => "Erreur lors de la création du compte",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(
        path: "/api/user/profile",
        tags: ["Users"],
        summary: "Récupération du profil utilisateur",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Profil récupéré",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "user", type: "object")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié")
        ]
    )]
    public function profile(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(["message" => "Veuillez vous connecter"], 401);
        }
        return response()->json(['user' => $user], 200);
    }

    #[OA\Post(
        path: "/api/user/logout",
        tags: ["Users"],
        summary: "Déconnexion utilisateur",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Déconnecté avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string")
                    ]
                )
            )
        ]
    )]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté avec succès'], 200);
    }

    #[OA\Put(
        path: "/api/profile/update",
        tags: ["Users"],
        summary: "Mise à jour du profil utilisateur",
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["name", "email"],
                    properties: [
                        new OA\Property(property: "name", type: "string", example: "John Doe"),
                        new OA\Property(property: "email", type: "string", example: "user@mail.com")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Profil mis à jour",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(property: "user", type: "object")
                    ]
                )
            ),
            new OA\Response(response: 500, description: "Erreur serveur")
        ]
    )]
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
        ]);

        try {
            $user->update($validated);
            return response()->json([
                'message' => 'Profil mis à jour avec succès',
                'user' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}