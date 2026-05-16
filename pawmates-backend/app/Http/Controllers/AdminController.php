<?php

namespace App\Http\Controllers;

use App\Models\User;

class AdminController extends Controller
{
    public function users()
    {
        return response()->json(User::all());
    }
}