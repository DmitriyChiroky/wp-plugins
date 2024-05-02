<?php


class TodoItem {

    private $id;
    private $title;
    private $completed;

    public function __construct($id, $title, $completed) {
        $this->id = $id;
        $this->title = $title;
        $this->completed = $completed;
    }

    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function isCompleted() {
        return $this->completed;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setCompleted($completed) {
        $this->completed = $completed;
    }
}
