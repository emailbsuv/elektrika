<?php


namespace App\Http\Controllers\Api\v1;


use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use App\Models\BusinessCard;
use Illuminate\Http\Request;

class BusinessCardController extends Controller
{
    /**
     * Get a listing of BusinessCards.
     *
     * @OA\Get(
     *     path="/business-cards/",
     *     description="Get BusinessCards.",
     *     tags={"Business-Cards"},
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                  @OA\Property(
     *                  property="data",
     *                  type="string",
     *                  example="[ {'id': 1, 'user_id': 1, 'can': 'Я все могу', 'looking': 'Новые заказы', 'waiting_call': 0, 'created_at': '1591557416', 'updated_at': '1591557416', 'rating': 4.5} ]"
     *                )
     *             )
     *         )
     *      ),
     * )
     *
     * @param $id
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index() {
        $businessCards = BusinessCard::all();
        return BaseResource::collection($businessCards);
    }

    /**
     * Creating a new BusinessCard.
     *
     * @OA\Post(
     *     path="/business-cards/create",
     *     description="Create a BusinessCard.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Business-Cards"},
     * @OA\RequestBody(
     *     request="CreateBusinessCards",
     *     required=true,
     *          @OA\MediaType(mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property( property="can", type="string", example="Я все могу" ),
     *                  @OA\Property( property="looking", type="string", example="Новые заказы" ),
     *                  @OA\Property( property="waiting_call", type="bolean", example=true )
     *             )
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *         )
     *      ),
     * )
     *
     * @param $id
     * @param Request $request
     * @return BaseResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request) {
        $rules = [
            'can' => 'required|string|max:200',
            'looking' => 'required|string|max:200',
        ];

        $this->validate($request, $rules);

        $user = auth()->user();
        if ($user) {

            $businessCard = BusinessCard::where('user_id', $user->id)->first();
            if ($businessCard) {
                return response()->json(['message' => 'Business Card already exist.'], 401);
            }

            $businessCard = new BusinessCard([
                'user_id' => $user->id,
                'can' => $request->input('can'),
                'looking' => $request->input('looking')
            ]);
            $businessCard->save();

            return new BaseResource($businessCard);
        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }

    /**
     * Update the Business Card.
     *
     * @OA\Post(
     *     path="/business-cards/update/",
     *     description="Update Business Card.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Business-Cards"},
     * @OA\RequestBody(
     *     request="UpdateObject",
     *     required=true,
     *          @OA\MediaType(mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property( property="can", type="string", example="Я все могу" ),
     *                  @OA\Property( property="looking", type="string", example="Новые заказы" ),
     *                  @OA\Property( property="waiting_call", type="bolean", example=true )
     *             )
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *         )
     *      ),
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request) {
        $user = auth()->user();
        if ($user) {

            $businessCard = BusinessCard::where('user_id', $user->id)->first();
            if (!$businessCard) {
                return response()->json(['message' => 'Business Card not found.'], 401);
            }

            $can = str_replace('"', '', $request->input('can'));
            if($can) { $businessCard->can = $can; }
            $looking = str_replace('"', '', $request->input('looking'));
            if($looking) { $businessCard->looking = $looking; }
//            $waitingCall = $request->input('waiting_call');
            $waitingCall = filter_var($request->input('waiting_call'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if($waitingCall) { $businessCard->waiting_call = $waitingCall; }

            $businessCard->save();

            return response()->noContent();
        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }

    /**
     * Remove the Business Card.
     *
     * @OA\Delete(
     *     path="/business-cards/delete",
     *     description="Remove the Business Card.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Business-Cards"},
     *     operationId="deleteBusinessCardsById",
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *         )
     *      ),
     * )
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function destroy() {
        $user = auth()->user();
        if ($user) {
            $businessCard = BusinessCard::where('user_id', $user->id)->first();
            if (!$businessCard) {
                return response()->json(['message' => 'Business Card not found.'], 401);
            }

            $businessCard->delete();
            return response()->noContent();

        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }
}
