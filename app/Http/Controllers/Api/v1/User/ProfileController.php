<?php


namespace App\Http\Controllers\Api\v1\User;


use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use App\Models\Fcm;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProfileController extends Controller
{

    /**
     * Update user
     *
     * @OA\Patch(
     *     path="/user/profile",
     *     description="Update user profile.",
     *     security={{"bearerAuth":{}}},
     *     tags={"user"},
     * @OA\RequestBody(
     *     request="Register",
     *     description="Update user profile",
     *     required=true,
     *     @OA\JsonContent(
     *          @OA\Property( property="name", type="string", maximum=191, example="User1" ),
     *          @OA\Property( property="email", type="string", maximum=191, example="user1@elektrika.dev.ksyste.ms" ),
     *          @OA\Property( property="password", type="string", minimum=6, example="Elektrika1" ),
     *          @OA\Property( property="contact_phone", type="string", example="79026921111" ),
     *          @OA\Property( property="contact_first_name", type="string", example="FirstName" ),
     *          @OA\Property( property="contact_middle_name", type="string", example="MiddleName" ),
     *          @OA\Property( property="contact_last_name", type="string", example="LastName" ),
     *          @OA\Property( property="vk_link", type="string", example="/id11111111" ),
     *          @OA\Property( property="ok_link", type="string", example="/id1111111" ),
     *          @OA\Property( property="fb_link", type="string", example="/id1111111111" ),
     *          @OA\Property( property="web", type="string", example="http://elektrika.dev.ksyste.ms/api/documentation" ),
     *          @OA\Property( property="notification_review_mail", type="boolean", example=true ),
     *          @OA\Property( property="notification_review_sms", type="boolean", example=true ),
     *          @OA\Property( property="notification_review_push", type="boolean", example=true ),
     *          @OA\Property( property="notification_claim_mail", type="boolean", example=true ),
     *          @OA\Property( property="notification_claim_sms", type="boolean", example=true ),
     *          @OA\Property( property="notification_claim_push", type="boolean", example=true ),
     *          @OA\Property( property="notification_offer_mail", type="boolean", example=true ),
     *          @OA\Property( property="notification_offer_sms", type="boolean", example=true ),
     *          @OA\Property( property="notification_offer_push", type="boolean", example=true ),
     *          @OA\Property( property="notification_order_mail", type="boolean", example=true ),
     *          @OA\Property( property="notification_order_sms", type="boolean", example=true ),
     *          @OA\Property( property="notification_order_push", type="boolean", example=true ),
     *     )
     * ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                  @OA\Property( property="user", type="string", example="{ id: 111, name: 'User1', email: 'email@example.com'}" )
     *             )
     *         )
     *      ),
     * )
     *
     * @param Request $request
     * @return BaseResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request)
    {
        $rules = [
            'name' => 'nullable|string|max:191',
            'email' => 'nullable|string|email|max:191|unique:users,email,' . $request->user()->id,
            'password' => 'nullable|string|min:8'
        ];

        $this->validate($request, $rules);

        $user = $request->user();

//        $userProfile = User::find($user->id);

        if ($request->input('name')) { $user->name = $request->input('name'); }
        if ($request->input('birthday')) { $user->birthday = Carbon::createFromTimestamp($request->input('birthday')); }
        if ($request->input('password')) { $user->password = bcrypt($request->input('password')); }

        if ($request->input('contact_phone')) { $user->contact_phone = $request->input('contact_phone'); }
        if ($request->input('contact_first_name')) { $user->contact_first_name = $request->input('contact_first_name'); }
        if ($request->input('contact_middle_name')) { $user->contact_middle_name = $request->input('contact_middle_name'); }
        if ($request->input('contact_last_name')) { $user->contact_last_name = $request->input('contact_last_name'); }
        if ($request->input('vk_link')) { $user->vk_link = $request->input('vk_link'); }
        if ($request->input('ok_link')) { $user->ok_link = $request->input('ok_link'); }
        if ($request->input('fb_link')) { $user->fb_link = $request->input('fb_link'); }
        if ($request->input('web')) { $user->web = $request->input('web'); }

        if ($request->input('notification_review_mail')) { $user->notification_review_mail = $request->input('notification_review_mail'); }
        if ($request->input('notification_review_sms')) { $user->notification_review_sms = $request->input('notification_review_sms'); }
        if ($request->input('notification_review_push')) { $user->notification_review_push = $request->input('notification_review_push'); }
        if ($request->input('notification_claim_mail')) { $user->notification_claim_mail = $request->input('notification_claim_mail'); }
        if ($request->input('notification_claim_sms')) { $user->notification_claim_sms = $request->input('notification_claim_sms'); }
        if ($request->input('notification_claim_push')) { $user->notification_claim_push = $request->input('notification_claim_push'); }
        if ($request->input('notification_offer_mail')) { $user->notification_offer_mail = $request->input('notification_offer_mail'); }
        if ($request->input('notification_offer_sms')) { $user->notification_offer_sms = $request->input('notification_offer_sms'); }
//        if ($request->input('notification_offer_push')) { $user->notification_offer_push = $request->input('notification_offer_push'); }
//        if ($request->input('notification_order_mail')) { $user->notification_order_mail = $request->input('notification_order_mail'); }
//        if ($request->input('notification_order_sms')) { $user->notification_order_sms = $request->input('notification_order_sms'); }
        $notificationOrderPush = filter_var($request->input('notification_order_push'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $user->notification_order_push = $notificationOrderPush;
        $notificationOrderMail = filter_var($request->input('notification_order_mail'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $user->notification_order_mail = $notificationOrderMail;
        $notificationOrderSms = filter_var($request->input('notification_order_sms'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $user->notification_order_sms = $notificationOrderSms;

//        $user->notification_order_push = false;

//        return response()->json(var_export($user));
//        $userProfile->notification_order_mail = false;
        $user->save();
//        return response()->json(compact('user'));
        return new BaseResource($user);
//        return new BaseResource(array('notificationOrderSms' => $notificationOrderSms, 'notificationOrderPush' => $notificationOrderPush));
    }

    /**
     * Send user avatar
     *
     * @OA\Post(
     *     path="/user/avatar",
     *     description="Send user avatar.",
     *     security={{"bearerAuth":{}}},
     *     tags={"user"},
     * @OA\RequestBody(
     *     request="SendAvatar",
     *     description="SendAvatar",
     *     required=true,
     *          @OA\MediaType(mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="avatar", type="file", collectionFormat="multi", @OA\Items(type="string", format="binary")),
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
     * @param Request $request
     * @return String
     * @throws \Illuminate\Validation\ValidationException
     */
    public function uploadAvatar(Request $request) {

        /*        $request->validate([
                    'avatar'=> ['required','image']
                ]);*/

        if(!$request->hasFile('avatar')) {
            return response()->json(['message' => 'Upload avatar not found'], 400);
        }

        $file = $request->file('avatar');

        if(!$file->isValid()) {
            return response()->json(['message' => 'Invalid avatar upload'], 400);
        }
        $user = auth()->user();
        if ($user) {
            $path = public_path() . '/images/avatars/';
            $fileName = $user->id.'-'.$file->getClientOriginalName();  // TODO Change file name to server generated
            $file->move($path, $fileName );
            $user->avatar = '/images/avatars/'.$fileName;
            $user->save();
            return response()->json(['avatar' => $user->avatar], 200);
//            return response()->noContent();
        } else {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }
    }
    /**
     * Delete User Avatar
     *
     * @OA\Delete(
     *     path="/user/avatar",
     *     description="Delete user avatar.",
     *     security={{"bearerAuth":{}}},
     *     tags={"user"},
     *
     *     @OA\Response(
     *          response=204,
     *          description="successful operation",
     *         )
     *      ),
     * )
     *
     * @param Request $request
     * @return App\Models\User
     */
    public function deleteAvatar(Request $request) {
        $user = auth()->user();
        if ($user) {
            $user->avatar = '';
            $user->save();
            return response()->noContent();
        } else {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }
    }

    /**
     * Update FCM
     *
     * @OA\Put(
     *     path="/user/updateFCM",
     *     description="Update user token for Firebase Cloud Message.",
     *     security={{"bearerAuth":{}}},
     *     tags={"user"},
     * @OA\RequestBody(
     *     request="UpdateFCM",
     *     description="UpdateFCM",
     *     required=true,
     *     @OA\JsonContent(
     *          @OA\Property(
     *              property="token",
     *              type="string",
     *              maximum=191,
     *              example="TokenFCM"
     *          ),
     *     )
     * ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *         )
     *      ),
     * )
     *
     * @param Request $request
     * @return App\Models\User
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateFCM(Request $request)
    {
        $user = auth()->user();

        $token = request('token');

        if ($user) {
            if (!$token) {
                return response()->json(['message' => 'Token field required.'], 400);
            }
            if ($user->fcm) {
                $user->fcm->token = $token;
                $user->fcm->save();

            } else {
                $fcm = new Fcm(['user_id' => $user->id, 'token' => $token]);
                $fcm->save();
            }

        } else {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        return response()->noContent();
    }

    /**
     * @OA\Get(
     *     path="/user/{userId}",
     *     description="Get user info by id.",
     *     tags={"user"},
     *     @OA\Parameter(
     *         description="ID of user to return",
     *         in="path",
     *         name="userId",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           format="int64"
     *         )
     *     ),
     *     @OA\Response(response="default", description="Get info about a user")
     * )
     *
     * Get the authenticated User.
     *
     * @param $id
     * @return BaseResource
     */
    public function info($id) {
//        $user = auth()->user();

//        if ($user) {
            $userProfile = User::find($id, ['id', 'name', 'contact_first_name', 'contact_middle_name', 'contact_last_name', 'avatar']);
            if ($userProfile) {
                return new BaseResource($userProfile);
            } else {
                return response()->json(['message' => 'User not found.'], 401);
            }
//        } else {
//            return response()->json(['message' => 'User not authenticated.'], 401);
//        }
    }
}
