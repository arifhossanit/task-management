@extends('layouts.master')

@section('title', 'My Tasks')

@section('content')
    <div class="container mt-4">
        <h2>My Tasks</h2>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <!-- Task Filters -->
        <form method="GET" action="{{ route('tasks.index') }}" class="mb-3">
            <div class="row">
                <!-- Filter by Status -->
                <div class="col-md-4">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <!-- Sort by Due Date -->
                <div class="col-md-4">
                    <select name="sort" class="form-select" onchange="this.form.submit()">
                        <option value="">Sort by Due Date</option>
                        <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>Ascending</option>
                        <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Descending</option>
                    </select>
                </div>
                <!-- <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div> -->
            </div>
        </form>

        <!-- Task List -->
        <div class="card">
            <div class="card-header">
                <a href="{{ route('tasks.create') }}" class="btn btn-success float-end">Add Task</a>
                <h4>Task List</h4>
            </div>
            <div class="card-body">
                @if ($tasks->isEmpty())
                    <p class="text-center">No tasks available. <a href="{{ route('tasks.create') }}">Create a new task</a>.</p>
                @else
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Due Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tasks as $task)
                                <tr>
                                    <td>{{ $task->title }}</td>
                                    <td>{{ $task->description }}</td>
                                    <td>{{ $task->status }}</td>
                                    <td>{{ $task->due_date->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this task?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
@endsection
