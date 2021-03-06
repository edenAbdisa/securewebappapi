<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\FeedbackType;
use App\Models\Address;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $feedback = Feedback::where('user_id', $user->id)->get()
                ->each(function ($item, $key) {
                    $item->feedback_types;
                });
            return response()
                ->json(
                    HelperClass::responeObject(
                        $feedback,
                        true,
                        Response::HTTP_OK,
                        'Successfully fetched.',
                        "feedback are fetched sucessfully.",
                        ""
                    ),
                    Response::HTTP_OK
                );
        } catch (ModelNotFoundException $ex) { // User not found
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'The model doesnt exist.', "", $ex->getMessage()),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        } catch (Exception $ex) { // Anything that went wrong
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'Internal server error.', "", $ex->getMessage()),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        }
    }
    public function review()
    {
        try {
            $feedback = Feedback::where('status', '=', 'active')->orWhereNull('status')->get()
                ->each(function ($item, $key) {
                    $item->user;
                    $item->feedback_types;
                });
            return response()
                ->json(
                    HelperClass::responeObject(
                        $feedback,
                        true,
                        Response::HTTP_OK,
                        'Successfully fetched.',
                        "feedback are fetched sucessfully.",
                        ""
                    ),
                    Response::HTTP_OK
                );
        } catch (ModelNotFoundException $ex) { // User not found
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'The model doesnt exist.', "", $ex->getMessage()),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        } catch (Exception $ex) { // Anything that went wrong
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'Internal server error.', "", $ex->getMessage()),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        }
    } 
    public function commentUpload(Request $request){
        $file=$request->file('file');
        $fileName=$file->getClientOriginalName();
        $finalName= date('His') . $fileName;
        $request->file('file')->storeAs('file/',$finalName,'public');
        return response()
                    ->json(
                        HelperClass::responeObject($finalName, true, Response::HTTP_CREATED, "Validation failed check JSON request", "WORKED","WORKED"),
                        Response::HTTP_CREATED
                    );
    }
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            $validatedData = Validator::make($request->all(), [
                'file' => ['max:30'],
                'comments' => ['required', 'max:30'],
                'feedback_name' => ['required'],
                'address.latitude' => [ 'numeric'],
                'address.longitude' => ['numeric'],
                'address.country' => [ 'max:50'],
                'address.city' => [ 'max:50'],
                'address.type' => [ 'max:10', Rule::in(['feedback'])]
            ]);
            if ($validatedData->fails()) {
                return response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_BAD_REQUEST, "Validation failed check JSON request", "", $validatedData->errors()),
                        Response::HTTP_BAD_REQUEST
                    );
            }
            
            $feedbacktype = FeedbackType::where('name', Str::ucfirst($request->feedback_name))->where('status','!=','deleted')
                ->first();
            if ($feedbacktype) {
                /* $address = $request->address;
                $address = new Address($address);
                $address->type = 'feedback';
                if (!$address->save()) {
                    return  response()
                        ->json(
                            HelperClass::responeObject(null, false, Response::HTTP_INTERNAL_SERVER_ERROR, "Address couldn't be saved.", "",  "Address couldn't be saved"),
                            Response::HTTP_INTERNAL_SERVER_ERROR
                        );
                } */
                
                $feedback = new Feedback($request->all());
                $feedback->user_id = $user->id; 
                $feedback->feedback_types_id = $feedbacktype->id;
                $feedback->status = "active"; 
                /* $file=$request->file('file');
                $fileName=$file->getClientOriginalName();
                $finalName= date('His') . $fileName;
                $request->file('file')->storeAs('file/',$finalName,'public');
                $feedback->file= $fileName; */
                if ($feedback->save()) {
                    return response()
                        ->json(
                            HelperClass::responeObject($feedback, true, Response::HTTP_CREATED, "Feedback saved", "Feedback is added", ""),
                            Response::HTTP_CREATED
                        );
                } else {
                    return response()
                        ->json(
                            HelperClass::responeObject($feedback, false, Response::HTTP_OK, "Internal error", "", "Feedback isn't saved."),
                            Response::HTTP_OK
                        );
                }
            } else {
                return response()
                    ->json(
                        HelperClass::responeObject($feedbacktype, false, Response::HTTP_BAD_REQUEST, "Feedback type doesnt exist.", "", "This feedback type doesnt exist."),
                        Response::HTTP_BAD_REQUEST
                    );
            }
        }
         catch (ModelNotFoundException $ex) {
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'The model doesnt exist.', "", $ex->getMessage()),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        } catch (Exception $ex) { // Anything that went wrong
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'Internal server error.', "", $ex->getMessage()),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        }
    } 
    public function search(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'name' => ['max:50'],
                'category_id' => ['numeric'],
                'status' => ['max:50'],
                'used_for' => ['max:70']
            ]);
            if ($validatedData->fails()) {
                return response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_BAD_REQUEST, "Validation failed check JSON request", "", $validatedData->errors()),
                        Response::HTTP_BAD_REQUEST
                    );
            }
            $input = $request->all();
            $types = Feedback::all();
            if ($types->count() <= 0) {
                return response()
                    ->json(
                        HelperClass::responeObject($types, true, Response::HTTP_OK, 'List of types.', "There is no type by the search.", ""),
                        Response::HTTP_OK
                    );
            }
            $col = DB::getSchemaBuilder()->getColumnListing('feedbacks');
            $requestKeys = collect($request->all())->keys();
            foreach ($requestKeys as $key) {
                if (in_array($key, $col)) {
                    if ($key == 'name') {
                        $input[$key] = Str::ucfirst($input[$key]);
                    }
                    $types = $types->where($key, $input[$key])->values();
                }
            }
            $types->each(function ($item, $key) {
            });
            return response()
                ->json(
                    HelperClass::responeObject($types, true, Response::HTTP_OK, 'List of types.', "List of types by this search.", ""),
                    Response::HTTP_OK
                );
        } catch (ModelNotFoundException $ex) { // User not found
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'The model doesnt exist.', "", $ex->getMessage()),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        } catch (Exception $ex) { // Anything that went wrong
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'Internal error occured.', "", $ex->getMessage()),
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
        }
    }
 
  public function update(Request $request, $id)
    {
        try { 
            $validatedData = Validator::make($request->all(), [
                'feedback_type' => ['max:30'],
                'comments' => ['max:70']
            ]);            
            $user = $request->user();

            if ($validatedData->fails()) {
                return response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_BAD_REQUEST, "Validation failed check JSON request", "", $validatedData->errors()),
                        Response::HTTP_BAD_REQUEST
                    );
            }
            $category_to_be_updated = Feedback::where('id', $id)->first();
            if (!$category_to_be_updated) {
                return response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_NOT_FOUND, 'Feedback doesnt exist.', "This feedback doesnt exist in the database.", ""),
                        Response::HTTP_OK
                    );
            }
if($category_to_be_updated->user_id!=$user->id){
return response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_NOT_FOUND, 'This feedback isnt this users.', "Wrong user.", "Wrong user."),
                        Response::HTTP_OK
                    );
}
            if ($category_to_be_updated->fill($request->all())->save()) {
                
                return response()
                    ->json(
                        HelperClass::responeObject($category_to_be_updated, true, Response::HTTP_CREATED, 'Feedback updated.', "The Feedback is updated.", ""),
                        Response::HTTP_CREATED
                    );
            } else {
                return response()
                    ->json(
                        HelperClass::responeObject($category_to_be_updated, false, Response::HTTP_INTERNAL_SERVER_ERROR, 'Internal error', "", "This Feedback couldnt be updated."),
                        Response::HTTP_INTERNAL_SERVER_ERROR
                    );
            }
        } catch (ModelNotFoundException $ex) { // User not found
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'The model doesnt exist.', "", $ex->getMessage()),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        } catch (Exception $ex) { // Anything that went wrong
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'Internal server error.', "", $ex->getMessage()),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        }
    }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
        try {
            $user = $request->user();
            $feedback = Feedback::find($id);
            if (!$feedback) {
                response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_NOT_FOUND, "Resource Not Found", '', "Request by this id doesnt exist."),
                        Response::HTTP_NOT_FOUND
                    );
            }
            if ($feedback->user_id!=$user->id) {
                response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_NOT_FOUND, "Resource Not Found", '', "You don't have this feedback."),
                        Response::HTTP_NOT_FOUND
                    );
            }
            if (strcmp($feedback->status,'active')!=0) {
                response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_NOT_FOUND, "Can't be deleted", '', "This resource is already reviewed and can't be deleted"),
                        Response::HTTP_NOT_FOUND
                    );
            }
            $feedback->delete();  
            return response()
                ->json(
                    HelperClass::responeObject(null, true, Response::HTTP_OK, 'Successfully deleted.', "Feedback is deleted sucessfully.", ""),
                    Response::HTTP_OK
                );
        } catch (ModelNotFoundException $ex) {
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'The model doesnt exist.', "", $ex->getMessage()),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        } catch (Exception $ex) { // Anything that went wrong
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'Internal error occured.', "", $ex->getMessage()),
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
        }
    }
}
