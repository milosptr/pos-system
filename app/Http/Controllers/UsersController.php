<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Resources\WaitersResource;

class UsersController extends Controller
{
    public function index()
    {
        return UserResource::collection(User::where('is_admin', 0)->get());
    }

    public function waiters()
    {
      return WaitersResource::collection(User::where('is_admin', 0)->get());
    }

    public function destroy($id)
    {
      $user = User::find($id);
      return $user->delete();
    }
}
