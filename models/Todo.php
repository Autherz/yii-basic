<?php 

namespace app\models;

use yii\base\Model;

class Todo extends Model
{
    public $title;

    private static $todo = [
        '111' => [
            'xxx' => 'xxx'
        ]
    ];

    public function find()
    {
        return self::$todo;
    }

    public function test()
    {
        return "test from todo";
    }
    
}