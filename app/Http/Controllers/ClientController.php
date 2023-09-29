<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{

    /**
     * Store a new client record in the database.
     *
     * @param  \Illuminate\Http\Request  $request  The HTTP request object containing the client data.
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {
        try{
            // Validate the incoming request data
            $validation = Validator::make($request->all(), [
                // 'client_id' => 'required|uuid',
                'email' => 'required|email|max:100',
                'phone_number' => 'nullable|string|max:10',
                'name' => 'required|string|max:100',
                'comment' => 'required|string|max:1000',
            ]);
    
            // Check if validation fails
            if ($validation->fails()) {
                $msg = $validation->errors()->first();
                return $this->sendFailureResponse($msg, config('constants.HTTP_CODE.UNPROCESSABLE_ENTITY'));
            }

            // Check if the email already exists in the database
            $existingClient = Client::emailExist($request->email);

            if ($existingClient) {
                // Return an error message
                return $this->sendFailureResponse(config('constants.COMMON.ERROR_MSG.EMAIL_EXIST'), config('constants.HTTP_CODE.UNPROCESSABLE_ENTITY')); 
            }
    
            // Extract and prepare the data for creating a new Client
            $requestData = $request->only([
                'client_id',
                'email',
                'phone_number',
                'name',
                'comment',
            ]);
            $requestData['client_id'] = Str::uuid()->toString();
            $result = Client::create($requestData); // Attempt to create a new Client record

            // Check if the record was created successfully
            if($result){
                return $this->sendSuccessResponse($requestData, config('constants.COMMON.MESSAGES.CLIENT_CREATED'), config('constants.HTTP_CODE.CREATED'));
            }

            // If creation fails, return a failure response
            return $this->sendFailureResponse(config('constants.ERROR_MSG.SOMETHING_WRONG'),config('constants.HTTP_CODE.SERVER_ERROR'));
        }catch (Exception $ex) {
            // Handle any exceptions that may occur during the process
            return $this->sendFailureResponse($ex->getMessage(), config('constants.HTTP_CODE.SERVER_ERROR'));
        }
    }

    /**
     * Retrieve a paginated list of clients with optional filtering and sorting.
     *
     * @param  \Illuminate\Http\Request  $request  The HTTP request object containing query parameters.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        try {
            // Get pagination parameters from the request
            $perPage = $request->input('per_page', 10);
            $currentPage = $request->input('page', 1);
            $skip = ($currentPage - 1) * $perPage;
            $search = $request->input('search');
            $sortField = $request->input('column', 'created_at'); // Default sort field
            $sortOrder = $request->input('direction', 'desc'); // Default sort order

            // Fetch a list of clients with pagination and sorting
            $data['clients'] = Client::getClient($perPage, $skip, $search, $sortField, $sortOrder);

            // Get the total count without pagination
            $totalCount = Client::getClient(null, null, $search ,$sortField, $sortOrder)->count();
            
            // Prepare data to be returned in the response
            $data['total'] = $totalCount;
            $data['skip'] = $skip;
            $data['per_page'] = $perPage;   
            $data['page'] = $currentPage;
 
            // Return a success response with the fetched data
            return $this->sendSuccessResponse($data, '');
        } catch (Exception $ex) {
            // Handle any exceptions that may occur during the process
            return $this->sendFailureResponse($ex->getMessage(), config('constants.HTTP_CODE.SERVER_ERROR'));
        }
    }

    /**
     * Display the client details for a given ID.
     *
     * @param  int $id The ID of the client to display.
     * @return \Illuminate\Http\JsonResponse The JSON response containing client data or an error message.
     */
    public function show($id) {
        try{
            // Attempt to find a client by the given $id
            $client = Client::find($id);

            // If the client with the specified ID is not found, return a failure response
            if (!$client) {
                return $this->sendFailureResponse(config('constants.COMMON.MESSAGES.CLIENT_NOT_FOUND'), config('constants.HTTP_CODE.NOT_FOUND'));
            }
            
            // If the client is found, return a success response with the client data
            return $this->sendSuccessResponse($client, '');
        }catch (Exception $ex) {
            // Handle any exceptions that may occur and return a failure response with the error message
            return $this->sendFailureResponse($ex->getMessage(), config('constants.HTTP_CODE.SERVER_ERROR'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request  The HTTP request object containing the updated data.
     * @param  int  $id  The ID of the client to update.
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id) {
        try{
            // Validate the incoming request data
            $validation = Validator::make($request->all(), [
                'email' => 'required|email',
                'phone_number' => 'nullable|string',
                'name' => 'required|string',
                'comment' => 'required|string|max:1000',
            ]);

            
            // Check if validation fails
            if ($validation->fails()) {
                $msg = $validation->errors()->first();
                return $this->sendFailureResponse($msg, config('constants.HTTP_CODE.UNPROCESSABLE_ENTITY'));
            }
    
            // Find the client by ID
            $client = Client::find($id);
            if (!$client) { // Check if the client exists
                return $this->sendFailureResponse([], config('constants.COMMON.MESSAGES.CLIENT_NOT_FOUND'), config('constants.HTTP_CODE.NOT_FOUND'));
            }

            // Check if the email already exists in the database
            $existingClient = Client::emailExist($request->email, $id);

            if ($existingClient) {
                // Return an error message
                return $this->sendFailureResponse(config('constants.COMMON.ERROR_MSG.EMAIL_EXIST'), config('constants.HTTP_CODE.UNPROCESSABLE_ENTITY')); 
            }
     
            // Extract and prepare the data for updating the client
            $requestData = $request->only([
                'email',
                'phone_number',
                'name',
                'comment',
            ]);
    
            // Update the client record
            $result = $client->update($requestData);
            if($result){ // Check if the update was successful
                return $this->sendSuccessResponse($request->all(), config('constants.COMMON.MESSAGES.CLIENT_UPDATED'));
            }

            // If update fails, return a failure response
            return $this->sendFailureResponse(config('constants.HTTP_CODE.SERVER_ERROR'));
        }catch (Exception $ex) {
            // Handle any exceptions that may occur during the process
            return $this->sendFailureResponse($ex->getMessage(), config('constants.HTTP_CODE.SERVER_ERROR'));
        }        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id  The ID of the client to delete.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id) {
        // Find the client by ID
        $client = Client::find($id);

        // Check if the client exists
        if (!$client) {
            return $this->sendFailureResponse(config('constants.COMMON.MESSAGES.CLIENT_NOT_FOUND'), config('constants.HTTP_CODE.NOT_FOUND'));
        }

        // Delete the client record
        $result = $client->delete();
        if($result){  // Check if the deletion was successful
            return $this->sendSuccessResponse([], config('constants.COMMON.MESSAGES.CLIENT_DELETED'));
        }
    }

    
}
