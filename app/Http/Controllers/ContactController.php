<?php

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        ContactSubmission::create($data);

        Mail::raw(
            "New contact submission from {$data['name']} ({$data['email']}):\n\n{$data['message']}",
            function ($message) use ($data) {
                $message->to(config('mail.from.address'))
                    ->subject('Contact form: '.$data['subject']);
            }
        );

        return back()->with('status', 'Thank you! We will reply shortly.');
    }
}
