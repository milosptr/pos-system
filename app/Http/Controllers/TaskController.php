<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Resources\TaskResource;
use Services\WorkingDay;
use Services\Pusher;

class TaskController extends Controller
{
    public function index()
    {
      return TaskResource::collection(Task::orderBy('id', 'desc')->get());
    }

    public function indexForToday()
    {
      $workingDay = WorkingDay::getWorkingDay();
      $tasks = Task::whereBetween('created_at', $workingDay)
        ->orWhere('done', 0)
        ->orderBy('id', 'desc')
        ->get();
      return TaskResource::collection($tasks);
    }

    public function store(Request $request)
    {
      Task::create($request->all());
      app(Pusher::class)->trigger('broadcasting', 'notifications', []);
      return TaskResource::collection(Task::orderBy('id', 'desc')->get());
    }

    public function finish($id, Request $request)
    {
      $task = Task::find($id);
      $task->update($request->all());
      app(Pusher::class)->trigger('broadcasting', 'notifications', []);
      return TaskResource::collection(Task::orderBy('id', 'desc')->get());
    }

    public function destroy($id)
    {
      $task = Task::find($id);
      $task->delete();
      app(Pusher::class)->trigger('broadcasting', 'notifications', []);
      return response(true, 200);
    }
}
