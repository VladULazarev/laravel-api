<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TasksResource;
use Illuminate\Http\JsonResponse;
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
     * @return TasksResource|JsonResponse
     */
    public function index(): TasksResource|JsonResponse
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
        if ( request()->filled('filter') && Auth::check() ) {

            [$criteria, $value] = explode(':', request('filter'));

            $tasks = TasksResource::collection(
                Task::where('user_id', Auth::id())
                ->where($criteria, 'like', "%$value%")
                ->latest('id')
                ->get()
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
        $tasks = TasksResource::collection(
            Task::where('user_id', Auth::user()->id)->latest('id')->paginate(5)
        );

        if (! count($tasks)) {
            return $this->error('', 'The resource you requested could not be found.', 404);
        }

        return $tasks;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreTaskRequest $request
     * @return TasksResource
     */
    public function store(StoreTaskRequest $request): TasksResource
    {
        $task = Task::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'description' => $request->description,
            'priority' => $request->priority
        ]);

        return new TasksResource($task);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse|TasksResource
     */
    public function show(int $id): TasksResource|JsonResponse
    {
        $task = Task::find($id);

        if (!$task) {
            return $this->error('', 'The resource you requested could not be found', 404);
        }

        if (!$this->isAuthorized($task)) {
            return $this->error('', 'You are not authorized to make this request', 403);
        }

        return new TasksResource($task);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  Task  $task
     * @return JsonResponse|TasksResource
     */
    public function update(Request $request, Task $task): TasksResource|JsonResponse
    {
        if (!$this->isAuthorized($task)) {
            return $this->error('', 'You are not authorized to make this request', 403);
        }

        $task->update($request->all());

        return new TasksResource($task);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $task = Task::find($id);

        if (!$task) {
            return $this->error('', 'The resource you requested could not be found', 404);
        }

        if (!$this->isAuthorized($task)) {
            return $this->error('', 'You are not authorized to make this request', 403);
        }

        $task->delete();

        return $this->success('', 'The resource was deleted', 200);
    }

    /**
     * Check if the user is authorized to make the current request (By default,
     * the current user can only manage his/her own tasks)
     *
     * @param Task $task
     * @return bool
     */
    private function isAuthorized(Task $task): bool
    {
        return Auth::id() === $task->user_id;
    }
}
