<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuthHelper;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function pruebas(): string
    {
        return 'accion de pruebas en user-controler';
    }

    public function register(Request $request): JsonResponse
    {
        $data = [
            'status' => 'Error',
            'code' => 404,
            'message' => 'El usuario NO se ha creado',
        ];
        //  recoger los datos por post
        $json = $request->input('json', null); // llega en una unica key "json"
        try {
            $params = json_decode($json, false, 512, JSON_THROW_ON_ERROR); // volcamos a un objeto
            $params_array = json_decode($json, true, 512, JSON_THROW_ON_ERROR); // asi a un array
        } catch (\JsonException $e) {
            // Handle JSON decoding errors here
            $data = [
                'status' => 'Error',
                'code' => 400,
                'message' => $e->getMessage(),
                'errors' => $e,
            ];

            return response()->json($data, $data['code']);
        }

        // limpiar datos
        $params_array = array_map('trim', $params_array);

        //  Validar datos
        $validate = \Validator::make($params_array,
            [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users', // validacion unigue con referencia a la tabla
                'password' => 'required',

            ]);

        if ($validate->fails()) {
            //si la validacion a fallado
            $data = [
                'status' => 'Error',
                'code' => 400,
                'message' => 'Error validacion de campos',
                'errors' => $validate->errors(),
            ];
        } else {
            // si ha pasado la validacion
            //  cifrar contraseña
            //$password_hash = password_hash($params_array['password'], PASSWORD_BCRYPT, ['cost' => 4]);
            //$password_hash = hash('sha256', $params_array['password']);
            // Crear usuario

            //$params_array['password'] = $password_hash;
            $params_array['role'] = 'ROLE_USER';

            // solo funciona con fillables
            $user = new User;
            $user->fill($params_array);
            $user->save();
            // funciona con todas las propiedaes
            // $user = new User;
            // $user->setAttribute('name', $params_array['name'] );
            // $user->setAttribute('surname', $params_array['surname'] );
            // $user->setAttribute('email', 'pedrollongo16@gmail.com' );
            // $user->setAttribute('role', 'ROLE_USER' );
            // $user->setAttribute('password', $params_array['password'] );

            // solo funciona con visibles
            // $user->surname = $params_array['surname'];
            // $user->email = $params_array['email'];
            // $user->password = $password_hash;
            // $user->role = 'ROLE_USER';
            // $user->save();

            $data = [
                'status' => 'Success',
                'code' => 200,
                'message' => 'El usuario se ha creado correctamente',
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function login(Request $request): string
    {
        $jwtAuth = new JwtAuthHelper();

        // recibir datos por post
        $json = $request->input('json', null); // llega
        // $en una unica key "json"
        try {
            $params = json_decode($json, false);
        } catch (\JsonException $e) {
            $data = [
                'status' => 'Error',
                'code' => 400,
                'message' => $e->getMessage(),
                'errors' => $e,
            ];

            return response()->json($data, $data['code']);
        }
        // volcamos a un objeto
        try {
            $params_array = json_decode($json, true);

        } catch (\JsonException $e) {
            $data = [
                'status' => 'Error',
                'code' => 400,
                'message' => $e->getMessage(),
                'errors' => $e,
            ];

            return response()->json($data, $data['code']);
        } //asi a un array

        // valdar datos
        $validate = \Validator::make($params_array,
            [
                'email' => 'required|email',
                'password' => 'required',

            ]);
        if ($validate->fails()) {
            //si la validacion a fallado
            $data = [
                'status' => 'Error',
                'code' => 400,
                'message' => 'Error validacion de campos',
                'errors' => $validate->errors(),
            ];
        } else {
            //cifrar la password
            //$password_hash = password_hash($params_array['password'], PASSWORD_BCRYPT, ['cost' => 4]);
            //             $password_hash = hash('sha256', $params_array['password']);            // devolver datos o token
            $testUser = new User;
            $testUser->fill($params_array);
            if (property_exists($params, 'getToken') and $params->getToken) {
                $data = $jwtAuth->signup($params->email, $params_array['password'], true);
                //$data['code'] = 200;
            } else {
                $data = $jwtAuth->signup($params->email, $params_array['password'], false);

            }

            return $data;
        }

        return response()->json($data, 200);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $page = User::query()->paginate();

        return response()->json(compact('page'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $item = new User;
        $item->fill($request->validated());
        $item->save();

        return response()->json(compact('item'));
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $item = User::query()->findOrFail($id);

        return response()->json(compact('item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws AuthenticationException
     */
    //public function update(int $id, UpdateUserRequest $request): JsonResponse
    public function update(UpdateUserRequest $request): JsonResponse
    {
        $jwtAuth = new JwtAuthHelper();
        $checkToken = $jwtAuth->checkToken( true);
        if ($checkToken) {
            echo 'Login correcto22';
            //echo 'checktoken: '.var_dump($checkToken);
        } else {
            echo 'login incorrecto22';
            //echo $checkToken;

        }
die();

        if (! $checkToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $item = User::query()->findOrFail($id);
        $item->update($request->validated());

        return response()->json(compact('item'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        return response()->json('Error', 400);
    }
}
