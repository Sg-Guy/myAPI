<?php

namespace App;

use OpenApi\Attributes as OA;

//Info App
#[OA\Info(
    version: "1.0.0",
    title: "MyAPI",
    description: "Documentation officielle de MyAPI"
)]


//Infos server
#[OA\Server(
    url:  "http://127.0.0.1:8000",
    description: "Serveur local"
)]

//Pour l'authentification
#[OA\SecurityScheme(
    securityScheme: "sanctum",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]

class OpenApi {}