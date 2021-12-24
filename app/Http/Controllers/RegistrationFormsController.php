<?php

namespace App\Http\Controllers;

use App\RegistrationForms;
use App\User;
use Exception;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class RegistrationFormsController extends Controller
{

    private $user;


    private function translateForView($key)
    {

        $arrayTranslate = [
            "id" => "ID da Ficha Toodobe",
            "registration_form_type" => "Tipo de Ficha",
            "user_id" => "ID do Usuário Toodobe",
            "name" => "Nome",
            "address_zipcode" => "CEP",
            "address_type" => "Tipo de residência",
            "address_type_other_string" => "Observação do Tipo de Residência",
            "address_street" => "Logradouro",
            "address_number" => "Número",
            "address_complement" => "Complemento",
            "address_neighborhood" => "Bairro",
            "address_city" => "Cidade",
            "address_state" => "UF",
            "address_country" => "Páis",
            "address_dwelling_time" => "Tempo de residência",
            "phone" => "Telefone",
            "marital_status" => "Estado Civíl",
            "marital_status_other_string" => "Observação do Tipo de Estado Civíl",
            "birthday" => "Data de Nascimento",
            "citizenship" => "Nacionalidade",
            "hometown" => "Cidade Natal",
            "mothers_name" => "Nome da Mãe",
            "fathers_name" => "Nome do pai",
            "professional_category" => "Categoria Profissional",
            "profession" => "Profissão",
            "proven_income" => "Renda",
            "pis" => "PIS",
            "fgts_value" => "FGTS",
            "employed" => "Empregado",
            "company_name" => "Nome da Empresa",
            "company_admission_date" => "Data de admissão",
            "declaring_ir" => "Declara Imposto de Renda",
            "education_level" => "Grau de Escolaridade",
            "educational_institution" => "Instituição de Ensino",
            "course" => "Curso",
            "conclusion_year" => "Ano de Conclusão",
            "has_vehicle" => "Possui Veículo",
            "vehicle_type" => "Tipo de Veículo",
            "vehicle_type_other_string" => "Observação do Tipo de Veículo",
            "vehicle_manufacturer" => "Fabricante do Veículo",
            "vehicle_model" => "Modelo do Veículo",
            "vehicle_year" => "Ano de Fabricação do Veículo",
            "own_property" => "Possui Propriedade",
            "property_value" => "Valor da Propriedade",
            "businessman" => "Possui empresa aberta",
            "businessman_name" => "Nome da Empresa",
            "businessman_cnpj" => "CNPJ",
            "approximate_billing" => "Faturamento Aproximado",
            "height" => "Altura",
            "weight" => "Peso",
            "personal_references" => "Referências Pessoais",
            "deleted_at" => "Ficha deletada em",
            "created_at" => "Ficha criada em",
            "updated_at" => "Ficha atualizada em"
        ];

        if (!isset($arrayTranslate[$key])) {
            return $key;
        }

        return $arrayTranslate[$key];

    }

    /**
     * @param array $array
     * @return array
     */
    private function clearArray(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_null($value) || $value == '')
                unset($array[$key]);
        }
        return $array;
    }

    /**
     * @param $string
     * @return false|string
     */
    private function checkIsADate($string)
    {
        if (is_string($string)) {
            if (preg_match("#^\d{4}-\d{2}-\d{2}#", $string)) {
                return date('d/m/Y', strtotime($string));
            } else {
                return ucfirst($string);
            }
        }
        return $string;
    }

    private function formatValueForView($value)
    {

        if (gettype($value) == "boolean") {
            return $value ? "Sim" : "Não";
        }

        if (is_array($value)) {
            $string = [];
            foreach ($value as $item){
               $string[] = implode(', ', $item);
            }
            return $string;
        }

        return $this->checkIsADate($value);
    }

    /**
     * @param RegistrationForms $registrationFrom
     * @return array
     */
    private function formatForView(RegistrationForms $registrationFrom)
    {
        $registrationFrom = $this->clearArray($registrationFrom->toArray());
        $return = [];
        foreach ($registrationFrom as $key => $value) {
            $return[$this->translateForView($key)] = $this->formatValueForView($value);
        }
        return $return;
    }

    public function showPrint($id)
    {
        $registrationForms = RegistrationForms::find($id);
        if ($registrationForms) {
            return view('ficha', ['data' => $this->formatForView($registrationForms)]);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $per_page = isset($request->per_page) ? $request->per_page : 10;


        if (!$this->isAdmin()) {
            $registrationForms = RegistrationForms::where('user_id', $this->user->id)
                ->when($request->get('search'), function ($query) use ($request) {
                    $query->where('name', 'LIKE', "%{$request->get('search')}%");
                })
                ->paginate($per_page)
                ->appends('per_page', $per_page);

        } else {
            $registrationForms = RegistrationForms::query()
                ->when($request->get('search'), function ($query) use ($request) {
                    $query->where('name', 'LIKE', "%{$request->get('search')}%");
                })
                ->paginate($per_page)
                ->appends('per_page', $per_page);
        }

        if ($request->get('search')) {
            $registrationForms->appends('search', $request->get('search'));
        }

        return $registrationForms;

    }

    private function isAdmin()
    {
        $this->setUser();
        return $this->user->role == 'admin';
    }

    private function setUser()
    {
        $this->user = Auth::user();
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function create(Request $request)
    {
        if ($this->isAdmin()) {
            $validator = Validator::make($request->all(), $this->validateRegistrationFormsRequest());

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            return $this->store($request);

        } else {
            return response()->json(['error' => 'Somente administradores podem criar este recurso'], 403);
        }
    }

    private function validateRegistrationFormsRequest()
    {
        return [
            'registration_form_type' => 'in:principal,conjuge',
            'user_id' => 'integer',
            'name' => 'string',
            'address_zipcode' => 'sometimes|formato_cep',
            'address_type' => 'sometimes|in:propria,familiares,alugada,outro',
            'address_street' => 'sometimes|string',
            'address_number' => 'sometimes|string',
            'address_complement' => 'sometimes|string',
            'address_neighborhood' => 'sometimes|string',
            'address_city' => 'sometimes|string',
            'address_state' => 'sometimes|string',
            'address_dwelling_time' => 'sometimes|integer',
            'phone' => 'sometimes|string',
            'marital_status' => 'sometimes|in:solteiro(a),casado(a) com. universal de bens,casado(a) com. parcial de bens,casado(a) com. separcao de bens,uniao estavel,separado judicialmente,divorciado(a),viuvo(a)',
            'birthday' => 'sometimes|date',
            'citizenship' => 'sometimes|string',
            'hometown' => 'sometimes|string',
            'mothers_name' => 'sometimes|string',
            'fathers_name' => 'sometimes|string',
            'professional_category' => 'sometimes|string',
            'profession' => 'sometimes|string',
            'proven_income' => 'sometimes|string',
            'pis' => 'sometimes|string',
            'fgts_value' => 'sometimes|string',
            'employed' => 'sometimes|boolean',
            'company_name' => 'sometimes|string',
            'company_admission_date' => 'sometimes|date',
            'declaring_ir' => 'sometimes|boolean',
            'education_level' => 'sometimes|in:sem instrução,ensino fundamental, ensino medio,ensino superior,pos graduacao, metrado,doutorado,mba',
            'educational_institution' => 'sometimes|string',
            'course' => 'sometimes|string',
            'conclusion_year' => 'sometimes|integer',
            'has_vehicle' => 'sometimes|boolean',
            'vehicle_type' => 'sometimes|in:moto,carro,caminhao,outro',
            'vehicle_manufacturer' => 'sometimes|string',
            'vehicle_model' => 'sometimes|string',
            'vehicle_year' => 'sometimes|integer',
            'own_property' => 'sometimes|boolean',
            'property_value' => 'sometimes|string',
            'businessman' => 'sometimes|boolean',
            'businessman_name' => 'sometimes|string',
            'businessman_cnpj' => 'sometimes|cnpj|formato_cnpj',
            'approximate_billing' => 'sometimes|string',
            'height' => 'sometimes|string',
            'weight' => 'sometimes|string',
            'personal_references' => 'sometimes|array'
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $request->merge(['user_name' => $this->resolveUserName($request->user_id)]);

        if ($this->userCan($request)) {
            return RegistrationForms::updateOrCreate(
                ['user_id' => $request->user_id, 'registration_form_type' => $request->registration_form_type],
                $request->all()
            );
        }
    }

    private function userCan($request)
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($request->user_id == $this->user->id) {
            return true;
        }

        return false;
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $object = RegistrationForms::find($id);

        if ($this->userIsOwner($object)) {
            if ($object) {
                return $object;
            } else {
                return response()->json(['error' => 'Nada foi encontrado com este ID'], 404);
            }

        }
        return response()->json(['error' => 'Você não possui permissão de acessar este recurso'], 403);

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
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function update(Request $request, $id)
    {
        $object = RegistrationForms::find($id);

        if ($this->userIsOwner($object)) {
            $validator = Validator::make($request->all(),
                array_merge($this->validateRegistrationFormsRequest(),
                    ['id' => 'required|integer']
                ));

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            return $this->store($request);

        } else {
            return response()->json(['error' => 'Você não possui permissão de acessar este recurso'], 403);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $object = RegistrationForms::find($id);

        if ($this->userIsOwner($object)) {
            if ($object) {
                return $object->delete();
            } else {
                return response()->json(['error' => 'Nada foi encontrado com este ID'], 404);
            }
        }
        return response()->json(['error' => 'Você não possui permissão de acessar este recurso'], 403);
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
