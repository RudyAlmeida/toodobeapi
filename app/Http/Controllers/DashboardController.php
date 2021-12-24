<?php

namespace App\Http\Controllers;

use App\Http\Controllers\NetworkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Carbon\Carbon;
use App\Http\Controllers\MenusController;



class DashboardController extends Controller
{
    private $user;

    private $networkController;

    /**
     * DashboardController constructor.
     */
    public function __construct()
    {

    }


    private function setUser()
    {
        $this->user = Auth::user();
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;

    }

    /**
     *
     */
    private function setNetWork()
    {
        $this->networkController = new NetworkController($this->user);
    }


    /**
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $this->setUser();

        if(!$this->userHasVerify($this->user)){
            return response()->json([
                'message' => 'Este Usuário não verificou seu email ainda',
                'resend-verify-email' => env('APP_URL').'/resend-verification-email/'. $this->user->email
            ], 400);
        }

        $this->setNetWork();

        return $this->formatDashboard();
    }

    /**
     * @return array
     */
    private function formatDashboard()
    {
        $menu = new MenusController();

        $response =  [
            'user' => [
                'profile' => $this->user,
                'menu' =>$menu->renderMenu(),
                'referred_link' => env('FRONTEND_URL').'/#/cadastro?affiliateCode='.$this->user->referred_code,
                'network' => $this->networkController->getLevels()
            ]
        ];

        $response['user']['profile']['age'] = Carbon::parse($this->user->birthday)->age;

        return $response;
    }



    /**
     * @param $user
     * @return bool
     */
    private function userHasVerify($user)
    {
        return $user->email_verified_at ? true : false;
    }
}
