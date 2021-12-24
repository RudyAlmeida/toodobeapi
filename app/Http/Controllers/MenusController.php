<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class MenusController extends Controller
{
    /**
     * @var Authenticatable|null
     */
    private $user;
    /**
     * @var array
     */
    private $influenciador = [

    ];

    private $admin = [

    ];

    /**
     * @var \string[][]
     */
    private $afiliado = [
        [
            'path' => '/projetos',
            'title' => 'Dados do Imóvel',
            'icon' => 'location_city',
            'class' => 'text-red'
        ],[
            'path' => '/assinaturas',
            'title' => 'Minhas assinaturas',
            'icon' => 'receipt',
            'class' => 'text-red'
        ], [
            'path' => '/cobrancas',
            'title' => 'Cobranças',
            'icon' => 'payment',
            'class' => 'text-red'
        ],
    ];

    private $default = [
        [
            'path' => '/dashboard',
            'title' => 'Dashboard',
            'icon' => 'dashboard',
            'class' => 'text-primary'
        ], [
            'path' => '/fichas-cadastrais',
            'title' => 'Dados Cadastrais',
            'icon' => 'person_add',
            'class' => 'text-red'
        ], [
            'path' => '/dados-bancarios',
            'title' => 'Dados Bancários',
            'icon' => 'monetization_on',
            'class' => 'text-red'
        ], [
            'path' => '/documentos',
            'title' => 'Documentos e comprovantes',
            'icon' => 'attachment',
            'class' => 'text-red'
        ],
        [
            'path' => '/marketing',
            'title' => 'Materiais de Divulgação',
            'icon' => 'shop',
            'class' => 'text-red'
        ],
        [
            'path' => '/suporte',
            'title' => 'Suporte',
            'icon' => 'chat',
            'class' => 'text-red'
        ],
    ];

    /**
     * MenusController constructor.
     */
    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function renderMenu()
    {
        if($this->user->role == 'admin'){
            return array_merge(
                $this->admin,
                $this->default,
                $this->influenciador,
                $this->afiliado
            );
        }
        $affiliate_type = $this->user->affiliate_type;
        return array_merge(
            $this->default,
            $this->$affiliate_type
        );

    }


}
