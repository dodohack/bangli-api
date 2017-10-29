<?php
/**
 * This is the backend middleware guarded auth controller.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use GuzzleHttp\Exception\ServerException;
Use GuzzleHttp\Client;

use App\Models\User;
use App\Models\Role;

class AuthController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /*
     * Process callback request from authentication server
     */
    public function postRegister(Request $request)
    {
        // TODO: Verify JWT before getting the payload
        if (!$this->createUser($request->input('email'))) {
            return response()->json(['error' => 'fail_to_register'], 500);
        }
        /*
                try {
                    // attempt to verify the credentials and create a token for the user
                    $token = $this->jwt->fromUser($user);
                } catch (JWTException $e) {
                    return response()->json(['error' => 'cannot_create_token'], 500);
                }
        */
        // all good, return status 200 to authentication server
        return response()->json(['status' => 'ok'], 200);
    }

    /*
     * Login a user with correctly permission into api server.
     * @return user - The user's basic information used for display purpose by
     *                client side.
     */
    public function login(Request $request)
    {
        // Middleware already takes care about user
        $user = $this->guard()->user();

        if (!$user) {
            // TODO: Verify if the user is already on auth server, if it is,
            // then create one on api server.
            $ret = $this->isUserOnAuthServer($this->guard()->getToken());
            if ($ret->email) {
                $user = $this->createUser($ret->email);
            } else {
                return $this->error("Invalid User");
            }
        }

        $result = $user->with(['role'])->first()->toArray();
        $ret = ['user' => $result, 'img_server' => env('IMG_SERVER')];
        return $this->success($ret);
    }

    /**
     * Invalid given JWT tokens
     * 1. post invalidate tokens request to bangli-auth server to ask it to
     * remove given tokens from bangli-auth.tokens table, and return those
     * tokens on success.
     * 2. add returned tokens to blacklist(call JWTAuth::invalidate).
     * @param Request $request
     */
    public function postInvalidateTokens(Request $request)
    {
        return $this->error('API unimplemented');
    }

    /**
     * Create a user with given email, and jwt token
     */
    private function createUser($email)
    {
        $user = new User;
        $payload = $this->guard()->parseToken()->getPayload();
        $user->uuid    = $payload->get('sub');
        $user->name    = $payload->get('aud');
        $user->role_id = Role::customer()->first()->id;
        $user->email   = $email;

        if ($user->save()) return $user;

        return false;
    }

    /**
     * Send a request to auth server to check if the user exists
     */
    private function isUserOnAuthServer($token)
    {
        $domainKey = str_replace('.', '_', $this->domain);
        $client = new Client();

        try {
            $res = $client->request('GET', config('auth.server') .
                'login-domain/' . $domainKey. '?token=' . $token);
        } catch (ServerException $e) {
            return false;
        }

        /* Read up to 1K data from returned body contains new user info or error */
        return json_decode($res->getBody()->read(1024));
    }

    /**
     * Get the guard to be used during authentication.
     * @return mixed
     */
    private function guard()
    {
        return Auth::guard();
    }
}