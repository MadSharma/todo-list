<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container">
        <h2>📌 To-Do List</h2>

        <!-- Show All Tasks Section -->
        <div class="show-all-section">
            <input type="checkbox" id="show-all-checkbox">
            <label for="show-all-checkbox">Show All Tasks</label>
        </div>

        <!-- Input Section -->
        <div class="input-section">
            <input type="text" id="task-title" placeholder="Enter a new task">
            <input type="file" id="task-image">
            <button id="add-task">Add</button>
        </div>

        <!-- Task List Table -->
        <div class="task-list">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all-tasks"></th>
                        <th>Task Name</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="task-list">
                    @foreach($tasks as $task)
                        <tr class="task" data-id="{{ $task->id }}">
                            <td><input type="checkbox" class="task-checkbox" data-id="{{ $task->id }}" {{ $task->completed ? 'checked' : '' }}></td>
                            <td><span class="{{ $task->completed ? 'completed' : '' }}">{{ $task->title }}</span></td>
                            <td>
                                @if($task->image)
                                    <img src="{{ asset('storage/' . $task->image) }}" alt="Task Image">
                                @else
                                    No Image
                                @endif
                            </td>
                            <td><button class="delete-task" data-id="{{ $task->id }}">Delete</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            // Add Task with Image
            $('#add-task').click(function() {
                var title = $('#task-title').val().trim();
                var imageFile = $('#task-image')[0].files[0];

                if (title === '') {
                    alert('Task title cannot be empty!');
                    return;
                }

                var formData = new FormData();
                formData.append('title', title);
                if (imageFile) formData.append('image', imageFile);

                $.ajax({
                    url: '/tasks',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        $('#task-list').append(`
                            <tr class="task" data-id="${data.id}">
                                <td><input type="checkbox" class="task-checkbox" data-id="${data.id}"></td>
                                <td><span>${data.title}</span></td>
                                <td>${data.image ? `<img src="{{ asset('storage/') }}/${data.image}" alt="Task Image">` : 'No Image'}</td>
                                <td><button class="delete-task" data-id="${data.id}">Delete</button></td>
                            </tr>
                        `);
                        $('#task-title').val('');
                        $('#task-image').val('');
                    },
                    error: function(err) {
                        alert('Error: ' + err.responseJSON.message);
                    }
                });
            });

            // Select/Deselect All Tasks
            $('#select-all-tasks').change(function() {
                $('.task-checkbox').prop('checked', $(this).prop('checked'));
            });

            // Show All Tasks
            $('#show-all-checkbox').change(function() {
                if ($(this).is(':checked')) {
                    $.get('/', function(data) {
                        $('#task-list').html($(data).find('#task-list').html());
                    });
                } else {
                    $('#task-list').empty();
                }
            });

            // Complete Task
            $(document).on('change', '.task-checkbox', function() {
                var taskId = $(this).data('id');
                var completed = $(this).is(':checked') ? 1 : 0; // Convert to integer

                $.ajax({
                    url: '/tasks/' + taskId,
                    type: 'PUT',
                    data: { completed: completed },
                    success: function(data) {
                        $('tr[data-id="' + taskId + '"] span').toggleClass('completed', completed);
                    }
                });
            });

            // Delete Single Task
            $(document).on('click', '.delete-task', function() {
                var taskId = $(this).data('id');
                if (!confirm('Are you sure you want to delete this task?')) return;

                $.ajax({
                    url: '/tasks/' + taskId,
                    type: 'DELETE',
                    success: function() {
                        $('tr[data-id="' + taskId + '"]').fadeOut(500, function() {
                            $(this).remove();
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
