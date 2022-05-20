<?php
namespace App\Data;

class CommentData {
    public $date;
    public $who;
    public $text;

    public function __construct($date, $who, $text) {
        $this->date = $date;
        $this->who = $who;
        $this->text = $text;
    }
}