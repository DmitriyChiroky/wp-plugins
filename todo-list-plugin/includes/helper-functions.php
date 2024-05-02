<?php


// Hook into admin_post action with a unique action name
add_action('admin_post_update_task', 'handle_update_task_form_submission');

function handle_update_task_form_submission() {
    // Check if the form has been submitted
    if (isset($_POST['update_task'])) {
        // Process form data
        $task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
        $new_title = isset($_POST['task_title']) ? sanitize_text_field($_POST['task_title']) : '';

        // Perform additional validation if needed

        // Update task title in the database
        $todo_list = new TodoList();
        $todo_list->updateTaskTitle($task_id, $new_title);

        // Redirect the user to another page or display a success message
        wp_redirect(admin_url('admin.php?page=todo-list&success=true'));
        exit;
    }
}
