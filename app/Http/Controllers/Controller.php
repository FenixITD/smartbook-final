<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0',
    title: 'SmartBook API',
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Local server',
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer'
)]
abstract class Controller
{
}
