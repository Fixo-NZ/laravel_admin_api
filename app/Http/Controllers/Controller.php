<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 * version="1.0.0",
 * title="Fixo API Documentation",
 * description="L5 Swagger API documentation for Fixo API",
 * @OA\Contact(
 * email="admin@example.com"
 * ),
 * @OA\License(
 * name="Apache 2.0",
 * url="http://www.apache.org/licenses/LICENSE-2.0.html"
 * )
 * )
 *
 * @OA\Server(
 * url=L5_SWAGGER_CONST_HOST,
 * description="Demo API Server"
 * )
 *
 * @OA\SecurityScheme(
 * securityScheme="bearerAuth",
 * type="http",
 * scheme="bearer"
 * )
 */

abstract class Controller
{
    //
}
