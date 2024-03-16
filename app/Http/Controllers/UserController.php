<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\JsonResponse;
use Validator;

class UserController extends Controller
{
    public  function  inscription (Request $request){

        $userData = $request->validate([
            "name" => ["required", "string", "min:2", "max:255"],
            "email" => ["required", "email", "unique:users,email"],
            "password" => ["required", "string", "min:8", "max:255","confirmed"]
        ]);

        $users = User::create([
            "name" => $userData["name"],
            "email" => $userData["email"],
            "password" => bcrypt($userData["password"])
        ]);

        return response($users, 201);
    }

    public function connexion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => ["required", "email"],
            "password" => ["required", "string", "min:8", "max:255"]
        ]);

        if($validator->fails()){
            return new JsonResponse( $validator->errors(), 401);
        }

        $users = User::where('email', $request->email)->first();

        if (!$users || !Hash::check($request->password, $users->password)) {
            return new JsonResponse(["message" => "Invalid credentials"], 401);
        }

        $token = $users->createToken("CLE_SECRETE")->plainTextToken;

        return new JsonResponse([
            "id" => $users->id,
            "name" => $users->name,
            "email" => $users->email,
            "token" => $token
        ], 200);
    }
}
