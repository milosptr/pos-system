<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\WaitersResource;

class UsersController extends Controller
{
    public function show($id)
    {
      return new UserResource(User::find($id));
    }

    public function index()
    {
      return UserResource::collection(User::where('is_admin', 0)->get());
    }

    public function update(Request $request, $id)
    {
      $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'username' => ['required', 'string', 'max:255'],
        'password' => [Rules\Password::defaults()],
      ]);
      $user = User::find($id);

      if($request->has('password')) {
        $password = Hash::make($request->get('password'));
        $user->update(['password' => $password]);
      }

      $user->update([
        'name' => $request->get('name'),
        'username' => $request->get('username'),
      ]);

      return new UserResource($user);
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
