<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(ContactRequest $request){
         $contact = Contact::create($request->validated());
        return response()->json(['message' => 'Message sent successfully!', 'data' => $contact], 201);
    }
}
