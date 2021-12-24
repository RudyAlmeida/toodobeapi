<?php

namespace App\Http\Controllers;

use App\Invites;
use App\Notifications\Invite as InviteEmail;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InvitesController extends Controller
{

    /*
     *
     */
    private $user;


    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $per_page = isset($request->per_page) ? $request->per_page : 10;
        $search = isset($request->search) ? $request->search : '';

        if (!$this->isAdmin()) {
            $invites = Invites::where('user_id', $this->user->id)
                ->where('name', 'like', $search . '%')
                ->paginate($per_page)
                ->appends('per_page', $per_page)
                ->appends('search', $search);

        } else {
            $invites = Invites::where('name', 'like', '%' . $search . '%')
                ->paginate($per_page)
                ->appends('per_page', $per_page)
                ->appends('search', $search);
        }

        return $invites;
    }

    /**
     * @return bool
     */
    private function isAdmin()
    {
        $this->setUser();
        return $this->user->role == 'admin';
    }

    /**
     *
     */
    private function setUser()
    {
        $this->user = Auth::user();
    }

    /**
     * @param Request $request
     * @return array|JsonResponse
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), $this->InviteValidation());

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if ($this->verifyIfUserExistInDatabase('email', $request->email)) {
            return response()->json(['error' => 'O email já se encontra registrado na nossa plataforma'], 400);
        }

        $this->setUser();

        $array = [
            'user_id' => $request->user_id,
            'user_name' =>  $this->resolveUserName($request->user_id),
            'track_code' => $this->generateTrack(),
            'referred_code' => $this->user->referred_code,
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'email_send_at' => Carbon::now()
        ];

        if (!$invite = $this->inviteAlreadyExist($array)) {
            $invite = Invites::updateOrCreate(
                [
                    'email' => $array['email'],
                    'user_id' => $array['user_id']
                ],
                $array
            );
        }

        $invite->notify(
            new InviteEmail($invite->track_code, $this->user->name)
        );

        return response()->json([
            'message' => 'O seu convite foi enviado!'
        ]);
    }

    /**
     * @return array
     */
    private function InviteValidation()
    {
        return [
            'name' => 'required|string',
            'user_id' => 'required|integer',
            'email' => 'required|email',
            'mobile' => 'required|celular_com_ddd',
        ];
    }

    /**
     * @param $field
     * @param $email
     * @return bool
     */
    private function verifyIfUserExistInDatabase($field, $email)
    {
        return (bool)User::where($field, $email)->first();
    }

    /**
     * @return false|string
     */
    private function generateTrack()
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $track_code = substr(str_shuffle(str_repeat($pool, 12)), 0, 60);
        if (Invites::where('track_code', $track_code)->first()) {
            $this->generateTrack();
        }
        return $track_code;
    }

    /**
     * @param $array
     * @return mixed
     */
    private function inviteAlreadyExist($array)
    {
        return Invites::where([
            'email' => $array['email'],
            'user_id' => $array['user_id']
        ])->first();
    }

    /**
     * @param $trackCode
     * @return JsonResponse|RedirectResponse|Redirector
     */
    public function track($trackCode)
    {
        $invite = Invites::where('track_code', $trackCode)->first();

        if ($invite) {
            $invite->update(['see_at' => Carbon::now()]);

            return redirect(
                env('FRONTEND_URL', 'https://app.toodobe.com/') . '/#/cadastro?affiliateCode=' . $invite->referred_code
            );

        }

        return response()->json(['error' => 'Seu convite não foi encontrado'], 404);
    }

    /*
     *
     */

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $object = Invites::find($id);

        if ($this->userIsOwner($object)) {
            if ($object) {
                return $object->delete();
            } else {
                return response()->json(['error' => 'Nada foi encontrado com este ID'], 404);
            }
        }
        return response()->json(['error' => 'Você não possui permissão para realizar esta ação'], 403);
    }

    private function userIsOwner($object)
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($object->user_id == $this->user->id) {
            return true;
        }

        return false;
    }

    /**
     * @param $user_id
     * @return mixed
     */
    private function resolveUserName($user_id)
    {
        return (User::find($user_id))->name;
    }
}
