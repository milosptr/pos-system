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
      return TaskResource::collection(Task::whereBetween('created_at', $workingDay)->orderBy('id', 'desc')->get());
    }

    public function store(Request $request)
    {
      Task::create($request->all());
      app(Pusher::class)->trigger('broadcasting', 'notifications', []);
      return TaskResource::collection(Task::orderBy('id', 'desc')->get());
    }

    public function finish($id)
    {
      $task = Task::find($id);
      $task->update(['done' => 1]);
      app(Pusher::class)->trigger('broadcasting', 'notifications', []);
      return TaskResource::collection(Task::orderBy('id', 'desc')->get());
    }

    public function destroy($id)
    {
      $task = Task::find($id);
      return $task->delete();
    }
}
