<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0',
    title: 'swagger',
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    description: 'Enter token from /api/login',
    bearerFormat: 'JWT',
    scheme: 'bearer',
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Local server',
)]
abstract class Controller
{
}
