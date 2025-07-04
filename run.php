<?php

require_once 'JSONDatabaseBuilder.php';

$db = new JSONDatabaseBuilder();

$db->table('users')->insert(['name' => 'Gabriel', 'email' => 'gabmicdev@gmail.com']);
$db->table('users')->insert(['name' => 'David', 'email' => 'david@gmail.com']);
$db->table('users')->insert(['id' => 7, 'name' => 'Emanuel', 'email' => 'emanuel@gmail.com']);

$allUsers = $db->table('users')->get();
print_r($allUsers);

$user1 = $db->table('users')->where('id', 1)->first(['name', 'email']);
print_r($user1);

$db->table('users')->where('id', 2)->update(['name' => 'Daniel']);

$db->table('users')->where('id', 7)->delete();

$final = $db->table('users')->get();
print_r($final);

$db->table('posts')->insert(['title' => 'My First Post']);

// Insert a video
$db->table('videos')->insert(['title' => 'Inception']);

// Insert comments related to post #1 and video #1 using morph fields
$db->table('comments')->insert([
    'content' => 'Great post!',
    'morph_type' => 'posts',
    'morph_id' => 1,
]);

$db->table('comments')->insert([
    'content' => 'Awesome video!',
    'morph_type' => 'videos',
    'morph_id' => 1,
]);

// Get all comments for post #1
$postComments = $db->table('posts')->morphMany('comments', 'morph_type', 'morph_id', 1);
print_r($postComments);

// Get the parent of comment #2 (which is a video)
$parent = $db->table('comments')->where('id', 2)->morphTo('morph_type', 'morph_id');
print_r($parent);