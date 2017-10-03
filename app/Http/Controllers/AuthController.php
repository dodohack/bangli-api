<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWTAuth;
use GuzzleHttp\Exception\ServerException;
Use GuzzleHttp\Client;

use App\Models\User;
use App\Models\Role;

class AuthController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
        parent::__construct();
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
     *  When login a user, just test of the user is created on the api
     * server, and return app domain specific user profile on success.
     * If the user is not created, it will query auth server for verification
     * and create one locally.
     */
    public function login(Request $request)
    {
        if (!$token = $this->jwt->setRequest($request)->getToken()) {
            return response('Unauthorized.', 401);
        }

        try {
            $user = $this->jwt->authenticate();
        } catch (TokenExpiredException $e) {
            return response('Token Expired.', 401);
        } catch (JWTException $e) {
            return response('Token Invalid.', 401);
        } catch (TokenInvalidException $e) {
            return response('Token Invalid.', 401);
        }

        if (!$user) {
            // TODO: Verify if the user is already on auth server, if it is,
            // then create one.
            $ret = $this->isUserOnAuthServer($this->jwt->getToken());
            if ($ret->email) {
                $user = $this->createUser($ret->email);
            } else {
                return response("Invalid User", 401);
            }
        }

        $tables = ['role',  'addresses'];
        // NOTE: We can't user $user->with($tables) here.
        $user = User::where('id', $user->id)->with($tables)->first()->toArray();
        // TODO: This should only used for bangli-admin-spa, frontend spa loads
        // img_server immediately after app starts.
        $ret = ['user' => $user, 'img_server' => env('IMG_SERVER')];
        return parent::success($request, json_encode($ret));
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

    }

    /**
     * Create a user with given email, and jwt token
     */
    private function createUser($email)
    {
        $user = new User;
        $payload = $this->jwt->parseToken()->getPayload();
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
}