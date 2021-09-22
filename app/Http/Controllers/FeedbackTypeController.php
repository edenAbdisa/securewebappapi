<?php

namespace App\Http\Controllers;

use App\Models\FeedbackType;
use Illuminate\Http\Request;
use Exception; 
use Gate; 
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
class FeedbackTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    } 

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{ 
            $validatedData = Validator::make($request->all(),[ 
                'name' => ['required','max:30'],  
            ]);
            if ($validatedData->fails()) {
                return response()
                ->json([
                    'data' =>null,
                    'success' => false,
                    'errors' => [
                        [
                            'status' => Response::HTTP_BAD_REQUEST,
                            'title' => "Validation failed check JSON request",
                            'message' => $validatedData->errors()
                        ],
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        $feedbacktype_old = FeedbackType::where('name', Str::ucfirst($request->name)) 
                    ->first();
        if (!$feedbacktype_old) {
            $feedbacktype = new FeedbackType($request->all());
            $feedbacktype->status="active"; 
                if ($feedbacktype->save()) { 
                    return response()
                    ->json([
                        'data' =>$feedbacktype,
                        'success' => true,
                        'content' => [
                            [
                                'status' => Response::HTTP_CREATED,
                                'title' => 'Type created.',
                                'message' => "The type is created sucessfully.",
                                'error'=>""
                            ],
                        ]
                    ], Response::HTTP_CREATED); 
                } else {
                    return response()
                        ->json([
                            'data' =>$feedbacktype ,
                            'success' => false,
                            'content' => [
                                [
                                    'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                                    'title' => 'Internal error',
                                    'message' => "This type couldnt be saved.",
                                    'error'=>"This type couldnt be saved."
                                ],
                            ]
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
        } else {
                return response()
                ->json([
                    'data' =>$feedbacktype_old ,
                    'success' => false,
                    'content' => [                        [
                            'status' => Response::HTTP_CONFLICT,
                            'title' => 'Feedback already exist.',
                            'message' => "This feedback already exist in the database.",
                            'error'=>""
                        ],
                    ]
                ], Response::HTTP_CONFLICT);  
        } 
    }catch (ModelNotFoundException $ex) { // User not found
        return response()
                ->json([
                    'success' => false,
                    'errors' => [
                        [
                            'status' => RESPONSE::HTTP_UNPROCESSABLE_ENTITY,
                            'title' => 'The model doesnt exist.',
                            'message' => $ex->getMessage()
                        ],
                    ]
                ], Response::HTTP_UNPROCESSABLE_ENTITY); 
    } catch (Exception $ex) { // Anything that went wrong
        return response()
                ->json([
                    'success' => false,
                    'errors' => [
                        [
                            'status' => 500,
                            'title' => 'Internal server error',
                            'message' => $ex->getMessage()
                        ],
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
    } 
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FeedbackType  $feedbackType
     * @return \Illuminate\Http\Response
     */
    public function show(FeedbackType $feedbackType)
    {
        //
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
            $types = FeedbackType::all();
            if ($types->count() <= 0) {
                return response()
                    ->json(
                        HelperClass::responeObject($types, true, Response::HTTP_OK, 'List of types.', "There is no type by the search.", ""),
                        Response::HTTP_OK
                    );
            }
            $col = DB::getSchemaBuilder()->getColumnListing('feedback_types');
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
                $item->category;
                $item->service;
                $item->item;
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
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FeedbackType  $feedbackType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FeedbackType $feedbackType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FeedbackType  $feedbackType
     * @return \Illuminate\Http\Response
     */
    public function destroy(FeedbackType $feedbackType)
    {
        //
    }
}
