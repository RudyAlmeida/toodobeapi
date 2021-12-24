<?php

namespace App\Http\Controllers;

use App\EmailVerify;
use App\Notifications\EmailVerifyRequest;
use App\Properties;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Image;
use App\Http\Controllers\Services\GoogleDrive;


class AuthController extends Controller
{

    private $user;

    /**
     * @return array
     */
    private function validateUser()
    {
        return [
            'name' => 'required|string',
            'mobile' => 'required|string|celular_com_ddd',
            'birthday' => 'required|date',
            'address_city' => 'required|string',
            'address_state' => 'required|string',
            'address_country' => 'required|string',
            'user_image' => 'sometimes|file',
        ];
    }

    private function validateUserUpdate()
    {
        return [
            'name' => 'sometimes|string',
            'mobile' => 'sometimes|string|celular_com_ddd',
            'birthday' => 'sometimes|date',
            'address_city' => 'sometimes|string',
            'address_state' => 'sometimes|string',
            'address_country' => 'sometimes|string',
            'user_image' => 'sometimes|file',
        ];
    }


    public function selfUpdate(Request $request)
    {

        $validator = Validator::make($request->all(), array_merge(
            $this->validateUserUpdate()),
            [
                'email' => 'sometimes|email',
                'registry_code' => 'sometimes|string|cpf|formato_cpf',
            ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $this->user = Auth::user();

        if ($request->hasFile('user_image')) {
            $photo = $request->file('user_image');
            $extension = $photo->getClientOriginalExtension();
            $photo = Image::make($photo)->resize(300, 300);
            $request->offsetUnset('user_image');
            $googleDrive = new GoogleDrive();
            $request->merge(['photo' => $googleDrive->uploadUserPhoto(
                $photo->stream($extension, 60),
                $this->user->registry_code. '-' . Str::of($this->user->name)->slug('-') . '.' . $extension)
            ]);
        }


        $request = $request->all();

        if (isset($request['user_image'])) {
            unset($request['user_image']);
        }


        //Não se muda a ROLE
        if (isset($request['role'])) {
            unset($request['role']);
        }


        //Não se muda o email
        if (isset($request['email'])) {
            unset($request['email']);
        }

        //Não se muda o registry_code
        if (isset($request['registry_code'])) {
            unset($request['registry_code']);
        }

        $databaseUser = User::find($this->user->id);
        $databaseUser->fill($request);
        $databaseUser->save();

        return $databaseUser;

    }

    private function validateAffiliateCode($affiliateCode)
    {
        return User::where('referred_code', $affiliateCode)->first();
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), array_merge(
            $this->validateUser(),
            [
                'email' => 'required|email|unique:users',
                'affiliate_type' => 'required|in:influenciador,afiliado,divulgador',
                'property_values_id' => 'sometimes|integer',
                'affiliate_code' => 'required|string',
                'registry_code' => 'required|unique:users|cpf|formato_cpf',
                'password' => 'required|string|confirmed',
            ])
        );

        $validator->setAttributeNames([
            'registry_code' => 'CPF',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if (!$this->validateAffiliateCode($request->affiliate_code)) {
            return response()->json(['error' => 'Código de indicação inválido'], 400);
        }


        $request = $request->all();

        if (!isset($request['role'])) {
            $request['role'] = 'customer';
        }

        if($request['affiliate_type'] == "divulgador"){
            $request['affiliate_type'] = "influenciador";
        }

        if(isset($request['property_values_id'])){

            $property = Properties::find($request['property_values_id']);

            if(!$property){
                return response()->json(['error' => 'id de tabela de imóveis inválido'], 400);
            }

            $request['property_value'] = $property->property_value;
            $request['first_installment_of_property'] = $property->first_installment;
            $request['last_installment_of_property'] = $property->last_installment;
            $request['expected_income'] = $property->income_value;
        }

        $request['photo'] = $this->avatarAPI($request);

        if ($user = User::create($request)) {
            return $this->verifyEmail($user);
        }

    }

    private function avatarAPI(array $request): string
    {
        $url = "https://www.avatarapi.com/avatar.asmx/GetProfile?email=" . $request['email'] . "&username=thiegocarvalho&password=c@my2005";
        $xml = simplexml_load_file($url);
        return isset($xml->Valid) && $xml->Valid == "true" ? $xml->Image->__toString() : 'https://ui-avatars.com/api/?size=181&background=FFA500&color=fff&name=' . $request['name'];
    }

    public function verifyEmail(User $user)
    {
        $emailVerify = EmailVerify::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => str_random(60)
            ]
        );

        if ($user && $emailVerify)
            $user->notify(
                new EmailVerifyRequest($emailVerify->token)
            );

        return response()->json([
            'message' => 'Um e-mail foi enviado para ' . $user->email . ' para confirmar seu cadastro'
        ]);
    }

    public function resendVerification($email)
    {
        $user = User::where('email', $email)->first();
        return $this->verifyEmail($user);
    }

    public function confirmEmail($token)
    {

        $emailVerify = EmailVerify::where('token', $token)->first();

        if (!$emailVerify)
            return response()->json([
                'error' => 'Este link de confirmação é inválido.'
            ], 404);

        $user = User::where('email', $emailVerify->email)->first();

        if (!$user)
            return response()->json([
                'error' => 'Email relacionado a este link não foi encontrado'
            ], 404);

        $user->email_verified_at = Carbon::now();
        $user->save();

        $emailVerify->delete();

        return response()->json([
            'message' => 'Email confirmado com sucesso'
        ]);


    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Não Autorizado'], 401);
        }

        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Carbon::now()
                ->locale('pt_BR')
                ->addMinutes(auth('api')->factory()->getTTL())
                ->format("d/m/Y H:i")
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Você saiu']);

    }
}
