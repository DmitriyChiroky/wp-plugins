<?php





// Создаем страницу в административном меню
function todo_list_menu_page() {
    add_menu_page(
        'Todo List',        // Заголовок страницы
        'Todo List',        // Название в меню
        'manage_options',   // Роль, необходимая для доступа
        'todo-list',        // Уникальный идентификатор страницы
        'todo_list_page_callback', // Функция обратного вызова для вывода содержимого страницы
        'dashicons-list-view' // Иконка для меню (необязательно)
    );
}
add_action('admin_menu', 'todo_list_menu_page');






// Функция обратного вызова для страницы списка задач
function todo_list_page_callback() {
    // Подключаем файл класса TodoList
    require_once plugin_dir_path(__FILE__) . '/../includes/class-todo-list.php';

    // Создаем экземпляр класса TodoList
    $todo_list = new TodoList();

    // Обработка действий пользователя
    if (isset($_POST['new_task'])) {
        $title = sanitize_text_field($_POST['task_title']);
        $todo_list->addItem($title);
    }

    if (isset($_POST['mark_completed'])) {
        $task_id = intval($_POST['task_id']);
        $todo_list->markItemCompleted($task_id);
    }

    if (isset($_POST['mark_not_completed'])) {
        $task_id = intval($_POST['task_id']);
        $todo_list->markItemNotCompleted($_POST['task_id']);
    }

    if (isset($_POST['delete_task'])) {
        $task_id = intval($_POST['task_id']);
        $todo_list->deleteItem($task_id);
    }

    // Получаем все задачи из базы данных
    $tasks = $todo_list->getAllItems();

?>

    <div class="wcl-todo-page wrap">
        <h1>Todo List</h1>

        <div class="data-inner">
            <div class="data-row">
                <h2>Список задач</h2>

                <ul class="data-list">
                    <?php foreach ($tasks as $task) : ?>
                        <li class="data-item">
                            <?php
                            $action = '';
                            if (isset($_GET['edit_task']) && $_GET['edit_task'] == $task->getId()) {
                                $action = esc_url(admin_url('admin-post.php'));
                            }
                            ?>
                            <form method="post" action="<?php echo $action; ?>">
                                <input type="hidden" name="task_id" value="<?php echo $task->getId(); ?>">

                                <?php if (isset($_GET['edit_task']) && $_GET['edit_task'] == $task->getId()) : ?>
                                    <div class="data-item-text-input">
                                        <input type="text" name="task_title" value="<?php echo $task->getTitle(); ?>">
                                    </div>
                                <?php else : ?>
                                    <div class="data-item-text">
                                        <span><?php echo $task->getTitle(); ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="data-item-forms">
                                    <?php if (!$task->isCompleted()) : ?>
                                        <div class="data-item-forms-item">
                                            <button type="submit" name="mark_completed" class="button button-completed">Отметить как завершенную</button>
                                        </div>
                                    <?php else : ?>
                                        <div class="data-item-forms-item">
                                            <input type="hidden" name="task_id" value="<?php echo $task->getId(); ?>">
                                            <button type="submit" name="mark_not_completed" class="button button-orange">Отметить как не завершенную</button>
                                        </div>
                                    <?php endif; ?>

                                    <div class="data-item-forms-item">
                                        <input type="hidden" name="task_id" value="<?php echo $task->getId(); ?>">
                                        <button type="submit" name="delete_task" class="button button-delete">Удалить</button>
                                    </div>

                                    <div class="data-item-forms-item">
                                        <?php if (isset($_GET['edit_task']) && $_GET['edit_task'] == $task->getId()) : ?>
                                            <button type="submit" name="update_task" class="button button-update">Обновить</button>
                                            <input type="hidden" name="action" value="update_task">
                                        <?php else : ?>
                                            <a href="?page=todo-list&edit_task=<?php echo $task->getId(); ?>" class="button button-edit">Изменить</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="data-row">
                <h2>Добавить новую задачу</h2>

                <form method="post">
                    <input type="text" name="task_title" placeholder="Введите название задачи" required>
                    <button type="submit" name="new_task" class="button button-primary">Добавить задачу</button>
                </form>
            </div>
        </div>
    </div>
<?php
}
