<?php
require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;

$contact = new Contact($PDO);

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['id'])
    && ($contact->find((int)$_POST['id'])) !== null
) {
    // Xóa file ảnh avatar khỏi thư mục uploads nếu có
    if (!empty($contact->avatar) && file_exists(__DIR__ . '/' . $contact->avatar)) {
        unlink(__DIR__ . '/' . $contact->avatar);
    }
    $contact->delete();
}

redirect('/');