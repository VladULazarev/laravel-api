<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreTaskRequest;
use App\Traits\HttpResponses;


class TaskController extends Controller
{
    use HttpResponses;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /**
         * Return 'collection' for the following requests with 'filter':
         *
         * http://laravel-api/api/tasks?filter=created_at:{created_at},
         * http://laravel-api/api/tasks?filter=name:{name},
         * http://laravel-api/api/tasks?filter=description:{description}
         * http://laravel-api/api/tasks?filter=priority:{priority}
         * We use 'like' in the 'where' clause
         */
        if ( request()->filled('filter') && Auth::user() ) {

            [$criteria, $value] = explode(':', request('filter'));

            $tasks = TaskResource::collection(
                Task::where('user_id', Auth::user()->id)
                ->where($criteria, 'like', "%$value%")
                ->orderBy('created_at', 'desc')
                ->paginate(5)
            );

            if (! count($tasks)) {
                return $this->error('', 'The resource you requested could not be found.', 404);
            }

            return $tasks;
        }

        /**
         * Return 'collection' for the following requests without 'filter':
         *
         * http://laravel-api/api/tasks,
         * http://laravel-api/api/tasks?page={page_number},
         */
        $tasks = TaskResource::collection(
            Task::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->paginate(5)
        );

        if (! count($tasks)) {
            return $this->error('', 'The resource you requested could not be found.', 404);
        }

        return $tasks;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\StoreTaskRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTaskRequest $request)
    {
        $task = Task::create([
            'user_id' => Auth::user()->id,
            'name' => $request->name,
            'description' => $request->description,
            'priority' => $request->priority
        ]);

        return new TaskResource($task);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $task = Task::find($id);

        if ( $this->checkTaskAndAuthorization($task) ) {
            return $this->checkTaskAndAuthorization($task);
        }

        return new TaskResource($task);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $task = Task::find($id);

        if ( $this->checkTaskAndAuthorization($task) ) {
            return $this->checkTaskAndAuthorization($task);
        }

        $task->update($request->all());

        return new TaskResource($task);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $task = Task::find($id);

        if ( $this->checkTaskAndAuthorization($task) ) {
            return $this->checkTaskAndAuthorization($task);
        }

        $task->delete();

        return $this->success('', 'The resource was deleted.', 200);
    }

    /**
     * Ckeck if the resource exists and the user is authorized to make
     * the current request
     *
     * @param  App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    private function checkTaskAndAuthorization($task)
    {
        if (! $task) {
            return $this->error('', 'The resource you requested could not be found', 404);
        }

        if ( Auth::user()->id !== $task->user_id ) {
            return $this->error('', 'You are not authorized to make this request', 403);
        }
    }
}
