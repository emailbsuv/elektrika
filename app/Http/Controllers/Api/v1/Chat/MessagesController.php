<?php


namespace App\Http\Controllers\Api\v1\Chat;


use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use App\Models\Dialog;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MessagesController extends Controller
{

    /**
     * Get the list of messages in the dialog.
     *
     * @OA\Get(
     *     path="/chat/dialog/{dialogId}/all",
     *     description="Get the list of messages in the dialog.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Chat"},
     *     @OA\Parameter(
     *         description="ID of user dialog",
     *         in="path",
     *         name="dialogId",
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
     *                  example="[ { 'id': 2, 'user_id': 1, 'text': 'text ', 'created_at': '1591112214' }]"
     *                )
     *             )
     *         )
     *      ),
     * )
     *
     * @return AnonymousResourceCollection
     */
    public function getAllMessages($id) {
        $user = auth()->user();
        if ($user) {
            $messages = Message::where('dialog_id', $id)->orderBy('created_at', 'asc')->take(200)->get(['id','user_id','text','created_at']);
            return BaseResource::collection($messages);
        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }

    /**
     * Get a list of recent messages in the dialog.
     *
     * @OA\Post(
     *     path="/chat/dialog/{dialogId}/all",
     *     description="Get a list of recent messages in the dialog.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Chat"},
     *     @OA\Parameter(
     *         description="ID of user dialog",
     *         in="path",
     *         name="dialogId",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           format="int64"
     *         )
     *     ),
     * @OA\RequestBody(
     *     request="DialogLast",
     *     required=true,
     *     @OA\JsonContent(
     *          @OA\Property(
     *              property="time",
     *              type="number",
     *              example="1591112214"
     *          )
     *     )
     * ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                  @OA\Property(
     *                  property="data",
     *                  type="string",
     *                  example="[ { 'id': 2, 'user_id': 1, 'text': 'text ', 'created_at': '1591112214' }]"
     *                )
     *             )
     *         )
     *      ),
     * )
     *
     * @param $id
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function postLastMessages($id, Request $request)
    {
        $params = request(['time', 'password']);

        $time = isset($params['time']) ? $params['time'] : null;
        if (!$time) {
            return response()->json(['message' => 'Invalid time field'], 400);
        }

        $user = auth()->user();
        if ($user) {
            $messages = Message::where('dialog_id', $id)->where('created_at', '>', Carbon::createFromTimestamp($time))->orderBy('created_at', 'asc')->take(1000)->get(['id', 'user_id', 'text', 'created_at']);
            return BaseResource::collection($messages);
        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }

    }

    /**
     * Send message to dialog.
     *
     * @OA\Post(
     *     path="/chat/dialog/{dialogId}/send",
     *     description="Send message to dialog.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Chat"},
     *     @OA\Parameter(
     *         description="ID of user dialog",
     *         in="path",
     *         name="dialogId",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           format="int64"
     *         )
     *     ),
     * @OA\RequestBody(
     *     request="SendMessage",
     *     description="Send Message",
     *     required=true,
     *     @OA\JsonContent(
     *          @OA\Property(
     *              property="text",
     *              type="string",
     *              example="new message"
     *          )
     *     )
     * ),
     *     @OA\Response(
     *          response=204,
     *          description="successful operation",
     *         )
     *      ),
     * )
     *
     * @param $id
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function sendMessage($id, Request $request) {
        $params = request(['text']);

        $text = isset($params['text']) ? $params['text'] : null;
        if (!$text) {
            return response()->json(['message' => 'Invalid text field'], 400);
        }

        $user = auth()->user();
        if ($user) {
            $dialog = Dialog::find($id);
            if ($dialog == null || !($dialog->from == $user->id || $dialog->to == $user->id)) {
                return response()->json(['message' => 'Dialog not found.'], 401);
            }
            $message = new Message(['dialog_id' => $dialog->id, 'user_id' => $user->id, 'text' => $text ]);
            $message->save();
            $dialog->last_message = $text;
            $dialog->save();
            return response()->noContent();
        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }
}
