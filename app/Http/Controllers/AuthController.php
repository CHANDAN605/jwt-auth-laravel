<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Auth;
use Validator;

class AuthController extends Controller
{
    //creating globle function
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['userlogin', 'register']]);
    }

    // User register
    public function register(Request $req)
    {
        //validating the request input
        $validator = Validator::make($req->all(), [
            'name' => 'required',
            'phone_number' => 'required|min:10|max:10',
            'email' => 'required|email|unique:users',
            'social_security_number' => 'required',
            'visa' => 'required',
            'visa_start_date' => 'required',
            'visa_end_date' => 'required',
            'profile_image' => 'required|image|mimes:jpeg,jpg,png,gif',
            'password' => 'required|min:8|confirmed',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Image uploading
        $file=$req->file('profile_image');
        $filename = date('YmdHi') . $file->getClientOriginalName();
        $file->move(public_path('./user_profile'), $filename);

        //Storing user to database    
        $user = User::create(array_merge(
            $validator->validated(),
            [
            'password' => Hash::make($req->password),
            'profile_image'=> $filename,
            ]
        ));

        return response()->json([
            'message'=>'User registred sussessfully',
            'user'=>$user,
        ],200);
    }

    // User login
    public function userlogin(Request $req)
    {
        //validating the request input
        $validator = Validator::make($req->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        // checking the user credintial is valid or not
        if(!$token = auth()->attempt($validator->validated())){
            return response()->json(['error'=>'Unauthorized'],401);
        }
        return $this->CreateNewToken($token);
    }

    // generating the token for user
    public function CreateNewToken($token){
        return response()->json([
            'access_token'=>$token,
            'token_type'=>'bearer',
            'user'=>auth()->user(),
        ]);
    }

    // get the user details
    public function userProfile(){
        return response()->json(auth()->user());
    }

    // get all the users in pagination vise
    public function getAllUsers(){
        $users=User::paginate(3);
        return response()->json($users);
    }


    // User logout 
    public function logout(){
        auth()->logout();
        return response()->json([
            'message'=>'User logout successfully',
        ]);
    }
}
