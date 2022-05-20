<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Cookie\CookieJar;

class LoginController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * ログインチェックする対象を変える
     *
     * @return void
     */
    protected function attemptLogin(Request $request)
    {
        $email = $request->input($this->username());
        $sub_id = $request->input('sub_id');
        $password = $request->input('password');

        if (!empty($request->input('remember'))) {
        	//\Cookie::queue('login_mail', $email, 120);
        	\Cookie::queue(cookie()->forever("login_mail",$email));
        	\Cookie::queue(cookie()->forever("login_sub_id",$sub_id));
        }

        if(empty($sub_id)){
            $sub_id = '';
        }

        $credentials = [ 'sub_id' => $sub_id, $this->username() => $email, 'password' => $password];

        return $this->guard()->attempt($credentials, $request->filled('remember'));
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected function authenticated(Request $request, $user)
    {
        $user->last_login_at = now();
        $user->save();
    }
}