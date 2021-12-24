<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use App\User;
use App\PasswordReset;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|string|email',
        ]);

        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user)
            return response()->json(
                ['error' => 'Não podemos encontrar um usuário com esse endereço de e-mail.'],
                404
            );

        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => str_random(60)
            ]
        );
        if ($user && $passwordReset)
            $user->notify(
                new PasswordResetRequest($passwordReset->token)
            );
        return response()->json([
            'message' => 'Enviamos seu link de redefinição de senha por e-mail!'
        ]);
    }

    /**
     * @param $token
     * @return JsonResponse
     */
    public function find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)->first();

        if (!$passwordReset)
            return response()->json([
                'error' => 'Este token de redefinição de senha é inválido.'
            ], 404);

        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();

            return response()->json([
                'error' => 'Este token de redefinição de senha é inválido.'
            ], 404);
        }
        return response()->json($passwordReset);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed',
            'token' => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }

        $checkEmail = PasswordReset::where([
            ['email', $request->email]
        ])->first();

        if (!$checkEmail)
            return response()->json([
                'error' => 'Este e-mail não solicitou recuperação de senha'
            ], 404);

        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->first();

        if (!$passwordReset)
            return response()->json([
                'error' => 'Este token de redefinição de senha é inválido.'
            ], 404);

        $user = User::where('email', $passwordReset->email)->first();

        if (!$user)
            return response()->json([
                'error' => 'Não conseguimos encontrar um usuário com esse endereço de email. '
            ], 404);

        $user->password = $request->password;
        $user->save();
        $passwordReset->delete();
        $user->notify(new PasswordResetSuccess());
        return response()->json($user);
    }
}
