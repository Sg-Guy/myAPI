<?php

namespace App;

use OpenApi\Attributes as OA;

//Info App
#[OA\Info(
    version: "1.0.0",
    title: "Shop API",
    description: "Documentation officielle de l'API Shop"
)]


//Infos server
#[OA\Server(
    url: "http://127.0.0.1:8000",
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