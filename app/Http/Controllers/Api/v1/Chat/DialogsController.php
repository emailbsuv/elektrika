<?php


namespace App\Http\Controllers\Api\v1\Chat;


use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use App\Models\Dialog;

class DialogsController extends Controller
{

    /**
     * Get a listing of user dialogs.
     *
     * @OA\Get(
     *     path="/chat/dialogs",
     *     description="Get user dialogs.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Chat"},
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                  @OA\Property(
     *                  property="data",
     *                  type="string",
     *                  example="[ { 'id': 1, 'from': 1, 'to': 2, 'last_message': 'It works, but not for sure', 'created_at': 1111123232, 'updated_at': '1591111081'  }]"
     *                )
     *             )
     *         )
     *      ),
     * )
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index() {
        $user = auth()->user();
        if ($user) {
            $dialogs = Dialog::where('from', $user->id)->orWhere('to', $user->id)->get();

            return BaseResource::collection($dialogs);
        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }

    /**
     * Create a new dialog.
     *
     * @OA\Get(
     *     path="/chat/dialog/create/{toId}",
     *     description="Get user dialogs.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Chat"},
     *     @OA\Parameter(
     *         description="TO userId",
     *         in="path",
     *         name="toId",
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
     *                  example="[ { 'data': { 'from': 1, 'to': '4', 'updated_at': '1591111081', 'created_at': '1591111081', 'id': 3, 'updated_at': '1591111081' } } ]"
     *                )
     *             )
     *         )
     *      ),
     * )
     *
     * @param $toId
     * @return BaseResource
     */
    public function create($toId) {
        $user = auth()->user();
        if ($user) {
            if ($toId == $user->id) {
                return response()->json(['message' => 'Split personality. ;)'], 401);
            }
            if ($toId < -1 || $toId == 0) {
                return response()->json(['message' => 'toId invalid)'], 401);
            }
            $dialogE = Dialog::where('from', $toId)->orWhere('to', $toId)->first();
            if ($dialogE) {
                return response()->json(['message' => 'Already created.'], 401);
            }
            $dialog = new Dialog(['from' => $user->id, 'to' => $toId]);
            $dialog->save();
            return new BaseResource($dialog);
        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }

}
