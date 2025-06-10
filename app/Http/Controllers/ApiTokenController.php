<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ApiTokenController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('customer.profile.api-token', compact('user'));
    }

    public function regenerate()
    {
        $user = User::find(Auth::user()->id);
        $user->api_token = Str::random(80);
        $user->save();

        return redirect()->back()->with('message', 'API Token has been regenerated successfully!');
    }
}
