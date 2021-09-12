<?php

namespace App\Http\Controllers;

use App\Http\Resources\AddressResource;
use Exception;
use Illuminate\Http\Request;
use Gate;
use App\Http\Resources\UserResource;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Address;
use App\Models\Membership;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{ 
    public function index()
    {
        $user = User::where('status','!=','deleted')
        ->orWhereNull('status')->get()
            ->each(function ($item, $key) {
                $item->address;
                $item->membership;
                $item->remember_token="";
            });
        return (new UserResource($user))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
      
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first(); 
        if ($user) {
            if (Hash::check($request->password, $user->password)) { 
                $token = $user->createToken('Laravel Password Grant',[$user->type])->accessToken;
                $user['remember_token']= $token; 
                if($user->save()){
                    $user->address;
                    $user->membership;
                 }
             
                return response(new UserResource($user), Response::HTTP_CREATED);            
            } else {
               return response()
                    ->json("Password mismatch", 422); 
            }
        } else {
            return response()
                    ->json("User doesnt not exist", 422); 
        }
    }
    public function logout(Request $request)
    {
        $token = $request->user()->token();
        //$token = User::where('email', $request->email)->first()->token();
        $token->revoke();
        $user = User::where('id', $token->user_id)->first();
        $user['remember_token'] = '';
        $response['message'] = $user->save() ? 'You have been successfully logged out!' : 'We could not successfully log out your account please try again!';
        return response($response, 200);
    }
     
    public function store(Request $request)
    {
        $input = $request->all();
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            $user = new User($input);
            $user->password = Hash::make($request->password);        
            $user->remember_token  = $user->createToken('Laravel Password Grant')->accessToken;
            $address = $request->address;
            $address = Address::create($address);
            if($user->type==="organization"){
                $user->status="pending";
            } 
            $user->status="active";
            try{
                $address->save();
            }catch(\Illuminate\Database\QueryException $ex){
                return response()
                    ->json($ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            try{
                $user->address_id = $address->id;
                $saveduser = $user->save();
                $user->address;
                $user->membership;
                return response(new UserResource($user), Response::HTTP_CREATED);
            }catch(\Illuminate\Database\QueryException $ex){
                return response()
                    ->json($ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            return response()
                ->json("An account already exist by this email.", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function search(Request $request)
    {
        $input = $request->all();
        $users = User::all();
        $col = DB::getSchemaBuilder()->getColumnListing('users');
        $requestKeys = collect($request->all())->keys();
        foreach ($requestKeys as $key) {
            if (empty($users)) {
                return response()->json($users, 200);
            }
            if (in_array($key, $col)) {
                $users = $users->where($key, $input[$key])->values();
            }
        }
        $users->each(function ($item, $key) {
            $item->address;
            $item->membership;
        });
        return response()->json($users, 200);
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();
        $user = User::where('id', $id)->first();
        if ($request->address) {
            $address_to_be_updated=$request->address;
            $address = Address::where('id', $user->bartering_location_id)->first(); 
            $address->city=$address_to_be_updated['city'];  
            $address->country=$address_to_be_updated['country']; 
            $address->latitude=(float)$address_to_be_updated['latitude'];  
            $address->longitude=(float)$address_to_be_updated['longitude'];        
            $address->save(); 
        } 
        $user=$user->fill($input);
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        if ($user->save()) {
            $user->address;
            $user->membership;
            return (new UserResource($user))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        }
    }
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()
                ->json("Resource Not Found", Response::HTTP_NOT_FOUND);
        }
        $user->status='deleted';
        $user->save();
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
