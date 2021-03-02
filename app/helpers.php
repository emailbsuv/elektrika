<?php

function my_objects_path($userId) {
    return storage_path() . '/my-objects/' . $userId % 100 . '/';
}

function my_orders_files_path($userId) {
    return storage_path() . '/files/' . $userId % 100 . '/';
}

function my_orders_photos_path($userId) {
    return storage_path() . '/photos/' . $userId % 100 . '/';
}
