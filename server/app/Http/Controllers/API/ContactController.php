<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\API\ApiController as ApiController;
use App\Models\Contact;
use App\Http\Resources\ContactResource;

class ContactController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $contacts = $user->contacts;
        return $this->sendResponse(ContactResource::collection($contacts), 'Contacts retrieved successfully.');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function store(Request $request)
    // {
    //     $input = $request->all();

    //     $validator = Validator::make($input, [
    //         'name' => 'required',
    //         'number' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->sendError('Validation Error.', $validator->errors());
    //     }

    //     // $user = Auth::user(); // Get the authenticated user

    //     // $contact = $user->contacts()->create($input);
    //     $Contact = Contact::create($input);

    //     return $this->sendResponse(new ContactResource($Contact), 'Contact created successfully.');
    // }


    public function showAll()
    {
        $contacts = Contact::all();

        return $this->sendResponse(ContactResource::collection($contacts), 'Contacts retrieved successfully.');
    }


    public function store(Request $request)
    {
        $user = $request->user();
        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => 'required',
            'number' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $contact = new Contact([
            'name' => $request->input('name'),
            'number' => $request->input('number'),
        ]);
        $user->contacts()->save($contact);
        return $this->sendResponse(new ContactResource($contact), 'Contact created successfully.');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $contacts = Contact::find($id);

        if (is_null($contacts)) {
            return $this->sendError('Contact not found.');
        }

        return $this->sendResponse(new ContactResource($contacts), 'Contact retrieved successfully.');
    }


    // public function searchByName(Request $request)
    // {
    //     $name = $request->input('name');

    //     $contacts = Contact::where('name', 'LIKE', "%$name%")
    //         ->where('user_id', auth()->id())
    //         ->get();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $contacts,
    //     ]);
    // }


    public function search($name)
    {
        $contacts = Contact::where('name', 'like', '%' . $name . '%')->get();
        if (is_null($contacts)) {
            return $this->sendError('Contact not found.');
        }

        // return $this->sendResponse(new ContactResource($contacts), 'Contact retrieved successfully.');
        return response()->json([
            'success' => true,
            'data' => $contacts,
        ]);
    }

    public function searchUsersContacts(Request $request, $name)
    {
        $searchTerm = $name;
        $user = $request->user();
        $contacts = Contact::where('user_id', $user->id)
            ->where('name', 'like', '%' . $name . '%')
            ->get();

        return response()->json([
            'contacts' => $contacts
        ]);
    }





    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, Contact $Contact)
    // {
    //     $input = $request->all();

    //     $validator = Validator::make($input, [
    //         'name' => 'required',
    //         'number' => 'required'
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->sendError('Validation Error.', $validator->errors());
    //     }

    //     $Contact->name = $input['name'];
    //     $Contact->number = $input['number'];
    //     $Contact->save();

    //     return $this->sendResponse(new ContactResource($Contact), 'Contact updated successfully.');
    // }



    public function update(Request $request, $id)
    {
        $user = $request->user();
        $contact = Contact::find($id);

        if ($contact && $contact->user_id === $user->id) {
            $contact->update($request->all());
            return $this->sendResponse(new ContactResource($contact), 'Contact updated successfully.');
        } else {
            return $this->sendError('Unable to update.');
        }
    }





    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $contact = Contact::find($id);

        if ($contact && $contact->user_id === $user->id) {
            $contact->delete();
            return $this->sendResponse(new ContactResource($contact), 'Deleted successfully.');
        } else {
            return $this->sendError('Unable to delete.');
        }
    }
}
