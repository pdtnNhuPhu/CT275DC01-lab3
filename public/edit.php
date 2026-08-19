<?php
require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;

$contact = new Contact($PDO);
$id = isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_VALIDATE_INT) : false;

if (!$id || !($contact->find($id))) {
    redirect('/');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactData = [
        'name' => $_POST['name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'notes' => $_POST['notes'] ?? '',
    ];

    $errors = $contact->validate($contactData);

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (!empty($contact->avatar) && file_exists(__DIR__ . '/' . $contact->avatar)) {
            unlink(__DIR__ . '/' . $contact->avatar);
        }

        $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . uniqid() . '.' . $extension;
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
            $contact->avatar = 'uploads/' . $fileName;
        }
    }

    if (empty($errors)) {
        $contact->fill($contactData);
        if ($contact->save()) {
            redirect('/');
        }
    }
}

include_once __DIR__ . '/../src/partials/header.php';
?>

<body>
  <?php include_once __DIR__ . '/../src/partials/navbar.php'; ?>

  <div class="container">
    <?php 
      $subtitle = 'Cập nhật liên hệ';
      include_once __DIR__ . '/../src/partials/heading.php'; 
    ?>

    <div class="row justify-content-center">
      <div class="col-md-8">
        <form action="/edit.php?id=<?= $contact->id ?>" method="POST" enctype="multipart/form-data" class="col-md-12">
          
          <div class="form-group mb-3">
            <label for="name" class="form-label">Họ Tên</label>
            <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" placeholder="Nhập họ tên" value="<?= html_escape($contact->name) ?>" required />
            <?php if (isset($errors['name'])): ?>
              <span class="invalid-feedback"><strong><?= $errors['name'] ?></strong></span>
            <?php endif; ?>
          </div>

          <div class="form-group mb-3">
            <label for="phone" class="form-label">Số Điện Thoại</label>
            <input type="text" name="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" id="phone" placeholder="Nhập số điện thoại" value="<?= html_escape($contact->phone) ?>" required />
            <?php if (isset($errors['phone'])): ?>
              <span class="invalid-feedback"><strong><?= $errors['phone'] ?></strong></span>
            <?php endif; ?>
          </div>

          <div class="form-group mb-3">
            <label for="notes" class="form-label">Ghi Chú</label>
            <textarea name="notes" id="notes" class="form-control <?= isset($errors['notes']) ? 'is-invalid' : '' ?>" placeholder="Nhập ghi chú (tối đa 255 ký tự)"><?= html_escape($contact->notes) ?></textarea>
            <?php if (isset($errors['notes'])): ?>
              <span class="invalid-feedback"><strong><?= $errors['notes'] ?></strong></span>
            <?php endif; ?>
          </div>

          <div class="form-group mb-3">
            <label for="avatarInput" class="form-label">Thay Đổi Avatar</label>
            <input type="file" name="avatar" id="avatarInput" class="form-control" accept="image/*" />
            <div class="mt-2">
              <?php if (!empty($contact->avatar) && file_exists(__DIR__ . '/' . $contact->avatar)): ?>
                <img id="imagePreview" src="/<?= html_escape($contact->avatar) ?>" alt="Avatar" class="rounded-circle" style="width: 70px; height: 70px; object-fit: cover;">
              <?php else: ?>
                <img id="imagePreview" src="#" alt="Avatar" class="rounded-circle d-none" style="width: 70px; height: 70px; object-fit: cover;">
              <?php endif; ?>
            </div>
          </div>

          <button type="submit" name="submit" id="submit" class="btn btn-primary">Cập Nhật</button>
          <a href="/" class="btn btn-default ms-2">Hủy</a>
        </form>
      </div>
    </div>
  </div>

  <?php include_once __DIR__ . '/../src/partials/footer.php'; ?>

  <script>
    document.getElementById('avatarInput').addEventListener('change', function(e) {
      const file = e.target.files[0];
      const preview = document.getElementById('imagePreview');
      if (file) {
        const reader = new FileReader();
        reader.onload = function(evt) {
          preview.src = evt.target.result;
          preview.classList.remove('d-none');
        }
        reader.readAsDataURL(file);
      }
    });
  </script>
</body>
</html>