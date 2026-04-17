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
    url:  "https://myapi-o37g.onrender.com",// "http://127.0.0.1:8000",
    description: "Serveur local"
)]

//Pour l'authentification
#[OA\SecurityScheme(
    securityScheme: "sanctum",
    type: "https",
    scheme: "bearer",
    bearerFormat: "JWT"
)]

class OpenApi {}