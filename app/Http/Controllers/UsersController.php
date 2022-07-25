<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Resources\WaitersResource;

class UsersController extends Controller
{
    public function index()
    {
        return UserResource::collection(User::all());
    }

    public function waiters()
    {
      return WaitersResource::collection(User::where('is_admin', 0)->get());
    }
}
