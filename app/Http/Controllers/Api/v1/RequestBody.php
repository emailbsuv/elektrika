<?php
/**
 * @OA\RequestBody(
 *     request="User",
 *     description="User object that needs to be added",
 *     required=true,
 *     @OA\JsonContent(ref="#/components/schemas/User"),
 *     @OA\MediaType(
 *         mediaType="application/xml",
 *         @OA\Schema(ref="#/components/schemas/User")
 *     )
 * )
 *
 * @OA\RequestBody(
 *     request="Login",
 *     description="Credentials",
 *     required=true,
 *     @OA\JsonContent(
 *          @OA\Property(
 *              property="email",
 *              type="string",
 *              example="user1@elektrika.dev.ksyste.ms"
 *          ),
 *          @OA\Property(
 *              property="password",
 *              type="string",
 *              example="Elektrika1"
 *          ),
 *          @OA\Property(
 *              property="phone",
 *              type="string",
 *              example="380661560470"
 *          ),
 *          @OA\Property(
 *              property="sms_code",
 *              type="string",
 *              example="001122"
 *          ),
 *          @OA\Property(
 *              property="vk_token",
 *              type="string",
 *              example="VKToken"
 *          ),
 *          @OA\Property(
 *              property="i_token",
 *              type="string",
 *              example="instagramToken"
 *          )
 *     )
 * )
 *
 */
