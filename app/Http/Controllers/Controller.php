<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0',
    title: 'swagger',
)]
/**
 * @OA\Info(
 * version="1.0.0",
 * title="My API Documentation",
 * description="Документация для API моего проекта"
 * )
 *
 * @OA\SecurityScheme(
 * securityScheme="bearerAuth",
 * in="header",
 * name="Authorization",
 * type="http",
 * scheme="bearer",
 * bearerFormat="Sanctum"
 * )
 */
abstract class Controller
{
}
