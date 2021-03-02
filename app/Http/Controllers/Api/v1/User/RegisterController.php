<?php


namespace App\Http\Controllers\Api\v1\User;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /*
        |--------------------------------------------------------------------------
        | Register Controller
        |--------------------------------------------------------------------------
        |
        | This controller handles the registration of new users as well as their
        | validation and creation. By default this controller uses a trait to
        | provide this functionality without requiring any additional code.
        |
        */

    use RegistersUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * User sign up
     *
     * @OA\Post(
     *     path="/user/register",
     *     description="Handle a registration request for the application.",
     *     tags={"user"},
     * @OA\RequestBody(
     *     request="Register",
     *     description="Register user",
     *     required=true,
     *     @OA\JsonContent(
     *          @OA\Property(
     *              property="name",
     *              type="string",
     *              maximum=191,
     *              example="User1"
     *          ),
     *          @OA\Property(
     *              property="email",
     *              type="string",
     *              maximum=191,
     *              example="user1@elektrika.dev.ksyste.ms"
     *          ),
     *          @OA\Property(
     *              property="password",
     *              type="string",
     *              minimum=6,
     *              example="Elektrika1"
     *          ),
     *          @OA\Property(
     *              property="vk_token",
     *              type="string",
     *              example="VKToken"
     *          ),
     *          @OA\Property(
     *              property="i_token",
     *              type="string",
     *              example="InstagramToken"
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
     *                  property="token",
     *                  type="string",
     *                  example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9"
     *                )
     *             )
     *         )
     *      ),
     * )
     */
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $credentials = request(['email', 'name', 'password', 'vk_token', 'i_token']);

        $email = isset($credentials['email']) ? $credentials['email'] : "";
        $vkToken = isset($credentials['vk_token']) ? $credentials['vk_token'] : null;
        $fbToken = isset($credentials['fb_token']) ? $credentials['fb_token'] : null;
        $password = isset($credentials['password']) ? $credentials['password'] : null;
        $name = isset($credentials['name']) ? $credentials['name'] : null;
        $newName = null;
        $newPassword = null;

        if ($vkToken) {
            $client = new Google_Client(['client_id' => config('auth.google_client_id')]);
            try {
                $payload = $client->verifyIdToken($vkToken);
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 400);
            }
            if ($payload) {
                $userEmail = $payload['email'];
                $userEmailVerified = $payload['email_verified'];
                if ($userEmailVerified) {
                    if ($userEmail == $email) {
                        $user = User::where('email', $userEmail)->first();
                        if ($user) {
                            return response()->json(['message' => 'User already exist.'], 401);
                        }
                        if (!$password) {
                            $newPassword = Hash::make(str_random(16));
                            $request->merge(array('password' => $newPassword));
                        }
                        $request->merge(array('google_attached' => true));
                    } else {
                        return response()->json(['message' => 'Invalid Google account.'], 401);
                    }
                } else {
                    return response()->json(['message' => 'User Email Not Verified.'], 401);
                }
            } else {
                return response()->json(['message' => 'Invalid Google ID token.'], 401);
            }
        }

        if ($fbToken) {
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
                // When Graph returns an error
                return response()->json(['message' => 'Graph returned an error: ' . $e->getMessage()], 401);
            } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                return response()->json(['message' => 'Facebook SDK returned an error: ' . $e->getMessage()], 401);
            }

            $fbId = $me->getId();

            if ($fbId) {
                $user = User::where('fb_id', $fbId)->first();
                if ($user) {
                    return response()->json(['message' => 'User already exist.'], 401);
                } else {
                    if (!$password) {
                        $newPassword = Hash::make(str_random(16));
                        $request->merge(array('password' => $newPassword));
                    }
                    if (!$name) {
                        $newName = $me->getName();
                    }
                    $request->merge(array('facebook_attached' => true));
                }
            } else {
                return response()->json(['message' => 'User not found.'], 401);
            }
        }

        event(new Registered($user = $this->create($request->all())));

//        if ($gToken) {
//            $user->google_attached = true;
//            if ($newPassword) $user->password = $newPassword;
//            $user->save();
//        }
        if ($fbToken) {
            $user->fb_id = $fbId;
            $user->facebook_attached = true;
            if ($newPassword) $user->password = $newPassword;
            if ($newName) $user->name = $newName;
            $user->save();
        }

        return $this->registered($request, $user);
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        $token = auth()->login($user);
        return response()->json(compact('token', 'user'));
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required_without_all:fb_token|string|max:191',
            'email' => 'required|string|email|max:191|unique:users',
            'password' => 'required_without_all:g_token,fb_token|string|min:8',
            'vk_token' => 'required_without_all:password,fb_token|string|min:16',
            'i_token' => 'required_without_all:g_token,password|string|min:16',
        ],
            [
                'email.unique' => 'Woops, this user already exist. Please, sign in or register other account!',
            ]
        );
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }
}
