<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Roles",
    description: "Gestion des rôles"
)]
class RoleController extends Controller
{
    #[OA\Get(
        path: "/api/roles",
        description: "Récupérer tous les rôles",
        tags: ["Roles"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des rôles récupérée avec succès",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "nom", type: "string", example: "Admin")
                        ]
                    )
                )
            )
        ]
    )]
    public function index()
    {
        $roles = Role::all();
        return response()->json(['data' => $roles], 200);
    }

    #[OA\Get(
        path: "/api/roles/show/{id}",
        description: "Récupérer un rôle par son ID",
        tags: ["Roles"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Rôle récupéré avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "Admin")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Rôle non trouvé"
            )
        ]
    )]
    public function show($id)
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json(['message' => 'Rôle non trouvé'], 404);
        }
        return response()->json(['data' => $role], 200);
    }

    #[OA\Post(
        path: "/api/roles/store",
        description: "Créer un rôle",
        tags: ["Roles"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["nom"],
                    properties: [
                        new OA\Property(property: "nom", type: "string", example: "Admin")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Rôle créé avec succès"
            ),
            new OA\Response(
                response: 422,
                description: "Nom déjà existant"
            ),
            new OA\Response(
                response: 500,
                description: "Erreur interne serveur"
            )
        ]
    )]
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|unique:roles,nom',
        ], [
            'nom.required' => 'Le nom du rôle est requis !',
            'nom.unique' => 'Ce nom de rôle existe déjà !',
        ]);

        $role = new Role();
        $role->nom = $request->nom;

        try {
            $role->save();
            return response()->json(['message' => 'Rôle créé avec succès', 'data' => $role], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erreur lors de la création', 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Put(
        path: "/api/roles/update/{id}",
        description: "Mettre à jour un rôle",
        tags: ["Roles"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["nom"],
                    properties: [
                        new OA\Property(property: "nom", type: "string", example: "Super Admin")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Mise à jour réussie"),
            new OA\Response(response: 404, description: "Rôle non trouvé"),
            new OA\Response(response: 422, description: "Nom déjà existant")
        ]
    )]
    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json(['message' => 'Rôle non trouvé'], 404);
        }

        $request->validate([
            'nom' => 'required|string|unique:roles,nom,' . $role->id,
        ], [
            'nom.required' => 'Le nom du rôle est requis !',
            'nom.unique' => 'Ce nom de rôle existe déjà !',
        ]);

        $role->nom = $request->nom;

        try {
            $role->save();
            return response()->json(['message' => 'Rôle mis à jour', 'data' => $role], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erreur lors de la mise à jour', 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Delete(
        path: "/api/roles/destroy/{id}",
        description: "Supprimer un rôle (uniquement si aucun utilisateur lié)",
        tags: ["Roles"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Rôle supprimé"),
            new OA\Response(response: 400, description: "Impossible de supprimer le rôle, des utilisateurs y sont liés"),
            new OA\Response(response: 404, description: "Rôle non trouvé")
        ]
    )]
    public function destroy($id)
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json(['message' => 'Rôle non trouvé'], 404);
        }

        $linkedUsers = User::where('role_id', $role->id)->count();
        if ($linkedUsers > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer ce rôle. Des utilisateurs y sont liés.'
            ], 400);
        }

        try {
            $role->delete();
            return response()->json(['message' => 'Rôle supprimé avec succès'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erreur lors de la suppression', 'error' => $e->getMessage()], 500);
        }
    }
}