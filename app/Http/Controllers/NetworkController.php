<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;


class NetworkController extends Controller
{
    /**
     * @var array
     */
    private $network = [
            'levels' => []
    ];

    /**
     * @var int
     */
    private $networkLevel = 1;

    /**
     * @var
     */
    private $user;

    /**
     * NetworkController constructor.
     * @param $user
     */
    public function __construct($user)
    {
        $this->user = $user;

    }

    /**
     * @return array
     */
    public function getLevels()
    {
        $affiliates = $this->getAffiliates($this->user->referred_code);
        $this->network['levels'][$this->networkLevel]['members'] = $affiliates;
        $this->network['levels'][$this->networkLevel]['statistics'] = $this->networkStatistics($affiliates);

        $this->networkLevel++;

        foreach ($affiliates as $affiliate) {
            $this->getAffiliatesRecursive($affiliate->referred_code);
        }

        $this->networkTotalStatistics();

        return $this->network;
    }

    private function getAffiliates($referred_code)
    {
        return User::where('affiliate_code', $referred_code)->get();
    }

    private function networkStatistics($affiliates)
    {
        $ativos = 0;
        $inativos = 0;

        if ($affiliates) {
            foreach ($affiliates as $affiliate) {
                if ($affiliate->status == "ativo") {
                    $ativos++;
                } else {
                    $inativos++;
                }

            }
        }

        return [
            'active' => $ativos,
            'pending' => $inativos,
            'total' => count($affiliates)
        ];

    }


    private function networkTotalStatistics()
    {
        $totalAtivos = 0;
        $totalInativos = 0;
        $totalTotal = 0;

        foreach ($this->network['levels'] as $level){
            $totalAtivos = $totalAtivos + $level["statistics"]['active'];
            $totalInativos = $totalInativos + $level["statistics"]['pending'];
            $totalTotal = $totalTotal + $level["statistics"]['total'];
        }

        $this->network['user_statistics'] = [
            'active' => $totalAtivos,
            'pending' => $totalInativos,
            'total' => $totalTotal
        ];

    }

    /**
     * @param $referred_code
     */
    private function getAffiliatesRecursive($referred_code)
    {
        if ($this->networkLevel <= env('MAX_NETWORK_LEVEL', 3)) {
            $affiliates = $this->getAffiliates($referred_code);
            $this->network['levels'][$this->networkLevel]['members'] = $affiliates;
            $this->network['levels'][$this->networkLevel]['statistics'] = $this->networkStatistics($affiliates);
        }

    }
}
