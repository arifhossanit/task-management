<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try{
            $tasks = Task::where('user_id', auth()->id())
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->sort, fn($q) => $q->orderBy('due_date', $request->sort))
            ->get();

            // Check if the request expects a JSON response
            if ($request->wantsJson()) {
                return response()->json($tasks, 200);
            }
            // Default response for web (view)
            return view('tasks.task',compact('tasks'));
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'errors' => $e->errors(),
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tasks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:Pending,Progress,Completed',
                'due_date' => 'required|date',
            ]);
        
            $task = Task::create(array_merge($validated, ['user_id' => auth()->id()]));
        
            // Check if the request expects a JSON response
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Task created successfully',
                    'task' => $task,
                ], 201);
            }
            // Default response for we
            return redirect()->route('tasks.index')->with('message','Task Created successfully');
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'errors' => $e->errors(),
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        
        $task = Task::find($task->id);

        if (!$task) {
            // Handle not found case
                return response()->json(['message' => 'Task not found'], 404);
            
        }
        
        // Handle response based on request type
            return response()->json([
                'message' => 'Task retrieved successfully',
                'task' => $task,
            ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        $task = Task::find($task->id);

        if (!$task) {
            // Handle not found case
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Task not found'], 404);
            }
            return redirect()->route('tasks.index')->with('error', 'Task not found');
        }
        
        // Handle response based on request type
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Task retrieved successfully',
                'task' => $task,
            ], 200);
        }

        // For web requests, return the edit view
        return view('tasks.edit',['task'=>$task]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        try{
            if ($task->user_id !== auth()->id()) {
                if ($request->wantsJson()) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        
            $validated = $request->validate([
                'title' => 'string|max:255',
                'description' => 'nullable|string',
                'status' => 'in:Pending,Progress,Completed',
                'due_date' => 'date',
            ]);
        
            $task->update($validated);
        
            // Check if the request expects a JSON response
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Task updated successfully',
                    'task' => $task,
                ], 200);
            }
            // Default response for web
            return redirect()->route('tasks.index')->with('message','Task Updated successfully!');
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'errors' => $e->errors(),
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        $task->delete();
    
        // Check if the request expects a JSON response
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Task deleted successfully'], 200);
        }
        // Default response for web
        return redirect()->back()->with('message','Task deleted successfully');
    }
}
