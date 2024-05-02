


document.addEventListener('DOMContentLoaded', function () {
  // Check if the edit_task parameter is present in the URL
  const urlParams = new URLSearchParams(window.location.search);

  const editTaskId = urlParams.get('edit_task');
  if (editTaskId) {
    // Find the input field with task_id equal to editTaskId
    const inputField = document.querySelector('input[name="task_id"][value="' + editTaskId + '"]');

    if (inputField) {
      // Get the task_title from the input field's parent form
      const taskTitleInput = inputField.closest('.data-item').querySelector('input[name="task_title"]');
      inputField.closest('.data-item').querySelector('input[name="task_title"]').focus();
      // Move the cursor to the end of the input field
      taskTitleInput.selectionStart = taskTitleInput.selectionEnd = taskTitleInput.value.length;
    }
  }
});
