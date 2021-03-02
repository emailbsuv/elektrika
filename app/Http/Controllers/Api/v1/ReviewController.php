<?php


namespace App\Http\Controllers\Api\v1;


use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Get a listing of reviews by order.
     *
     * @OA\Get(
     *     path="/reviews/{id}",
     *     description="Get reviews.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Review"},
     *     @OA\Parameter(
     *         description="ID of object to return",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                  @OA\Property(
     *                  property="data",
     *                  type="string",
     *                  example="[ { 'id': 1, 'object_id': 1, 'user_id': 1, 'text': 'Test Commnet', 'created_at': '1591546165', 'updated_at': '1591546165'} ]"
     *                )
     *             )
     *         )
     *      ),
     * )
     *
     * @param $id
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index($id) {
        if(!$id) {
            return response()->json(['message' => 'Invalid id field'], 422);
        }
        $user = auth()->user();
        if ($user) {
            $proposals = Review::where('object_id', $id)->get();
            return BaseResource::collection($proposals);
        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }

    /**
     * Creating a new review.
     *
     * @OA\Post(
     *     path="/reviews/{id}/create",
     *     description="Create a review.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Review"},
     *     @OA\Parameter(
     *         description="ID of object to return",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           format="int64"
     *         )
     *     ),
     * @OA\RequestBody(
     *     request="CreateReview",
     *     required=true,
     *          @OA\MediaType(mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property( property="text", type="string", example="My review" )
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
    public function create($id, Request $request) {
        $rules = [
            'text' => 'required|string'
        ];

        $this->validate($request, $rules);

        $user = auth()->user();
        if ($user) {

            $review = new Review([
                'object_id' => $id,
                'user_id' => $user->id,
                'text' => $request->input('text')
            ]);
            $review->save();

            return new BaseResource($review);
        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }
}
