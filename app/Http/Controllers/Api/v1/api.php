<?php
/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="ElectrikA Api",
 *      description="ElectrikA Swagger OpenApi description",
 *      @OA\Contact(
 *          email="admin@ksyste.ms"
 *      )
 * )
 */
/**
 *  @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="L5 Swagger OpenApi dynamic host server"
 *  )
 *
 */
/**
 * @OA\SecurityScheme(
 *      description="Authorization token",
 *      securityScheme="bearerAuth",
 *      in="header",
 *      name="bearerAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT",
 * )
 *
 * @OA\Tag(
 *     name="auth",
 *     description="Authorization operations",
 * )
 *
 * @OA\Tag(
 *     name="user",
 *     description="Operations about user",
 * )
 *
 * @OA\Tag(
 *     name="static",
 *     description="Static content",
 * )
 *
 *
 *
 *
 * @OA\Get(
 *     path="/../../jsons/v1/config.json",
 *     description="Get default app config.",
 *     tags={"Static"},
 *     @OA\Response(
 *          response=200,
 *          description="successful operation",
 *         )
 *      ),*
 * )
 *
 */
