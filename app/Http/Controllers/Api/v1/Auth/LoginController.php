<?php


namespace App\Http\Controllers\Api\v1\Auth;


use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PhoneConfirmation;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Log;
use Notification;
use VK\Client\VKApiClient;
use VK\Exceptions\VKApiException;
use VK\Exceptions\VKClientException;
use VK\OAuth\Scopes\VKOAuthUserScope;
use VK\OAuth\VKOAuth;
use VK\OAuth\VKOAuthDisplay;
use VK\OAuth\VKOAuthResponseType;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct() {
    }

    /**
     * @OA\Get(
     *     path="/user/me",
     *     description="Get the authenticated User.",
     *     tags={"user"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="default", description="Get info about me")
     * )
     *
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth()->user();

        return response()->json(compact('user'));
    }

    /**
     * Request verification code.
     *
     * @OA\Post(
     *     path="/auth/request-code",
     *     description="Request verification code.",
     *     tags={"auth"},
     *  @OA\RequestBody(
     *      request="requestCode",
     *      required=true,
     *          @OA\MediaType(mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="phone", type="string", example="73832637672" ),
     *             )
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *      ),
     * )
     *
     * @param Request $request
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function requestCode(Request $request) {
        $this->validate($request, [
            'phone' => 'required|between:11,15'
        ]);

        $credentials = request(['phone']);
        $phone = $credentials['phone'];

        $user = User::where('phone', $phone)->first();

        if ($user) {
            $currentDateTime = Carbon::now();
            if( $currentDateTime->diffInMinutes( $user->code_created_at ) > 5 ){
                $user->notify(new PhoneConfirmation($user->generateVerificationCode()));
            }

        } else {
            $user = new User(['name' => $phone, 'phone' => $phone]);
            $user->password = User::generatePassword();
            $user->save();
            $user->notify(new PhoneConfirmation($user->generateVerificationCode()));
        }

        return response()->noContent();
//        return response()->json(['message' => 'Invalid login credential.'], 401);
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     description="Get a JWT via given credentials",
     *     tags={"auth"},
     *     @OA\RequestBody(ref="#/components/requestBodies/Login"),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                  @OA\Property(
     *                  property="token",
     *                  type="string",
     *                  example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9"
     *                )
     *             )
     *         )
     *      ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid username/password supplied"
     *     )
     * )
     */
    /**
     * Get a JWT via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = request(['email', 'password', 'vk_token', 'i_token', 'phone', 'sms_code']);

        $vkToken = isset($credentials['vk_token']) ? $credentials['vk_token'] : null;
        $instagramToken = isset($credentials['i_token']) ? $credentials['i_token'] : null;

        $phone = isset($credentials['phone']) ? $credentials['phone'] : null;
        $verificationCode = isset($credentials['sms_code']) ? $credentials['sms_code'] : null;

        if ($phone && $verificationCode) {
            $user = User::where('phone', $phone)->where('verification_code', $verificationCode)->first();
            if ($user != null) {
                $token = auth()->login($user);
                return response()->json(compact('token', 'user'));
            } else {
                if (config('app.debug') == true &&  $verificationCode == '001122') {
                    $user = User::where('phone', $phone)->first();
                    $token = auth()->login($user);
                    return response()->json(compact('token', 'user'));
                }
                return response()->json(['message' => 'User not found.'], 401);
            }
        }

        if ($vkToken) {
            $vk = new VKApiClient();
            try {
                $response = $vk->secure()->checkToken($vkToken, ['ip' => $request->getClientIp(), 'client_secret' => config('auth.vk_secret')]);
                return response()->json(['message' => var_export($response, tru)], 200);
            } catch (VKApiException $e) {
                return response()->json(['message' => $e->getMessage()], 401);
            } catch (VKClientException $e) {
                return response()->json(['message' => $e->getMessage()], 401);
            }
//            $response = $vk->users()->get($vkToken, array(
//                'user_ids' => array(1, 210700286),
//                'fields' => array('city', 'photo'),
//            ));

//            var_dump($response, $request->getClientIp());

/*
            $oauth = new VKOAuth();
            $client_id = 1234567;
            $redirect_uri = 'https://example.com/vk';
            $display = VKOAuthDisplay::PAGE;
            $scope = array(VKOAuthUserScope::WALL, VKOAuthUserScope::GROUPS);
            $state = 'secret_state_code';
            $revoke_auth = true;

            $browser_url = $oauth->getAuthorizeUrl(VKOAuthResponseType::TOKEN, $client_id, $redirect_uri, $display, $scope, $state, null, $revoke_auth);
*/

        }

/*        if ($gToken) {
            $client = new Google_Client(['client_id' => config('auth.google_client_id')]);
            try {
                $payload = $client->verifyIdToken($gToken);
            } catch (\Exception $e) {
                $this->writeStat(false);
                return response()->json(['message' => $e->getMessage()], 400);
            }
            if ($payload) {
                $userEmail = $payload['email'];
                $userEmailVerified = $payload['email_verified'];
                $user = User::where('email', $userEmail)->first();
                if ($user != null && $userEmailVerified && $user->google_attached) {
                    $token = auth()->login($user);
                    $this->writeStat(true, $user->id);
                    return response()->json(compact('token', 'user'));
                } else {
                    $this->writeStat(false);
                    return response()->json(['message' => 'User not found.'], 401);
                }
            } else {
                $this->writeStat(false);
                return response()->json(['message' => 'Invalid ID token.'], 401);
            }
        }*/

        /*if ($fbToken) {
            $fb = new \Facebook\Facebook([
                'app_id' => config('auth.fb_app_id'),
                'app_secret' => config('auth.fb_app_secret'),
                'default_graph_version' => 'v2.10',
                //'default_access_token' => '{access-token}', // optional
            ]);

            try {
                // Get the \Facebook\GraphNodes\GraphUser object for the current user.
                // If you provided a 'default_access_token', the '{access-token}' is optional.
                $response = $fb->get('/me?fields=id,name', $fbToken);
                $me = $response->getGraphUser();
            } catch(\Facebook\Exceptions\FacebookResponseException $e) {
                $this->writeStat(false);
                // When Graph returns an error
                return response()->json(['message' => 'Graph returned an error: ' . $e->getMessage()], 401);
            } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                $this->writeStat(false);
                // When validation fails or other local issues
                return response()->json(['message' => 'Facebook SDK returned an error: ' . $e->getMessage()], 401);
            }

            $fbId = $me->getId();
//            echo 'Logged in as ' . $me->getName();

//            return response()->json(['message' => 'Logged in as ' . $me->getName() . ' id:' . $me->getId()], 401);

            if ($fbId) {
                $user = User::where('fb_id', $fbId)->first();
                if ($user && $user->facebook_attached) {
                    $token = auth()->login($user);
                    $this->writeStat(true, $user->id);
                    return response()->json(compact('token', 'user'));
                } else {
                    $this->writeStat(false);
                    return response()->json(['message' => 'User not found.'], 401);
                }
            } else {
                $this->writeStat(false);
                return response()->json(['message' => 'User not found.'], 401);
            }
        }*/

        if (!isset($credentials['password'])) {
            return response()->json(['message' => 'Invalid login credential.'], 400);
        }

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'Invalid login credential.'], 401);
        }

        $user = $request->user();

        Log::info('User -> notify');
        $user->notify(new PhoneConfirmation('1234'));
//        Notification::send([$user], new PhoneConfirmation('380665969083', '1234'));

        return response()->json(compact('token', 'user'));
    }

    /**
     * Refresh a token.
     * @OA\Post(
     *     path="/login/refresh",
     *     description="Refresh a token.",
     *     tags={"auth"},

     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                  @OA\Property(
     *                  property="token",
     *                  type="string",
     *                  example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9"
     *                )
     *             )
     *         )
     *      ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid username/password supplied"
     *     )
     * )
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = auth()->refresh();

        return response()->json(compact('token'));
    }

    /**
     * Log the user out.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
