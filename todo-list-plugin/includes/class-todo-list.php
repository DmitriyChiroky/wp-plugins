<?php


class TodoList {

    private $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function createTable() {
        $table_name = $this->wpdb->prefix . 'todo_list';

        $sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL,
            `completed` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $this->wpdb->query($sql);
    }

    public function getAllItems() {
        $table_name = $this->wpdb->prefix . 'todo_list';
        $sql = "SELECT * FROM `{$table_name}` ORDER BY created_at ASC";
        $results = $this->wpdb->get_results($sql);

        $items = [];
        foreach ($results as $row) {
            $item = new TodoItem($row->id, $row->title, $row->completed);
            $items[] = $item;
        }

        return $items;
    }

    public function addItem($title) {
        $table_name = $this->wpdb->prefix . 'todo_list';
        $data = array(
            'title' => $title,
            'completed' => 0,
        );

        $this->wpdb->insert($table_name, $data);
    }

    public function markItemCompleted($id) {
        $table_name = $this->wpdb->prefix . 'todo_list';
        $data = array(
            'completed' => 1,
        );

        $this->wpdb->update($table_name, $data, array('id' => $id));
    }


    public function markItemNotCompleted($id) {
        $table_name = $this->wpdb->prefix . 'todo_list';
        $data = array(
            'completed' => 0,
        );

        $this->wpdb->update($table_name, $data, array('id' => $id));
    }

    public function deleteItem($id) {
        $table_name = $this->wpdb->prefix . 'todo_list';
        $this->wpdb->delete($table_name, array('id' => $id));
    }

    public function updateTaskTitle($id, $new_title) {
        $table_name = $this->wpdb->prefix . 'todo_list';
        $data = array(
            'title' => $new_title,
        );

        $where = array('id' => $id);

        $this->wpdb->update($table_name, $data, $where);
    }
}
