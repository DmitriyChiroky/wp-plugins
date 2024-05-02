<?php
/*
Plugin Name: Todo List Plugin
Description: Добавляет функционал списка задач.
Version: 1.0
Author: DmitriyShyrokiy
*/

// Подключаем файл класса TodoList
require_once plugin_dir_path(__FILE__) . 'includes/class-todo-list.php';

// Подключаем файл класса TodoItem
require_once plugin_dir_path(__FILE__) . 'includes/class-todo-item.php';

// Подключаем файл класса TodoItem
require_once plugin_dir_path(__FILE__) . 'includes/helper-functions.php';

// Загрузка стилей для административной части
add_action('admin_enqueue_scripts', 'todo_list_plugin_styles');
function todo_list_plugin_styles($hook) {
    if ('toplevel_page_todo-list' != $hook) {
        return;
    }

    wp_enqueue_script('todo-list-plugin-scripts', plugin_dir_url(__FILE__) . 'js/scripts.js', array('jquery'), '1.0.0', true);
    wp_enqueue_style('todo-list-plugin-styles', plugin_dir_url(__FILE__) . 'css/styles.css');
}

// Подключаем файл для административной части
require_once plugin_dir_path(__FILE__) . 'admin/admin-page.php';
