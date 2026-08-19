<?php
require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;
use CT275\Labs\Paginator;

$contact = new Contact($PDO);

$limit = (isset($_GET['limit']) && is_numeric($_GET['limit'])) ? (int)$_GET['limit'] : 5;
$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;

$paginator = new Paginator(
    recordsPerPage: $limit,
    totalRecords: $contact->count(),
    currentPage: $page
);

$contacts = $contact->paginate($paginator->recordOffset, $paginator->recordsPerPage);
$pages = $paginator->getPages(length: 3);

include_once __DIR__ . '/../src/partials/header.php';
?>

<body>
  <?php include_once __DIR__ . '/../src/partials/navbar.php'; ?>

  <!-- Main Page Content -->
  <div class="container">
    <?php 
      $subtitle = 'Danh sách tất cả liên hệ';
      include_once __DIR__ . '/../src/partials/heading.php'; 
    ?>

    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <a href="/add.php" class="btn btn-primary">
            <i class="fa fa-plus"></i> Thêm Liên Hệ Mới
          </a>
          <span>Tổng số: <strong><?= $paginator->totalRecords ?></strong> liên hệ</span>
        </div>

        <!-- Table Contacts -->
        <table id="contacts" class="table table-bordered table-responsive table-striped">
          <thead>
            <tr>
              <th style="width: 80px;" class="text-center">Avatar</th>
              <th>Họ Tên</th>
              <th>Số Điện Thoại</th>
              <th>Ngày Tạo</th>
              <th>Ghi Chú</th>
              <th style="width: 160px;" class="text-center">Hành Động</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($contacts)): ?>
              <?php foreach ($contacts as $c): ?>
                <tr>
                  <td class="text-center align-middle">
                    <?php if (!empty($c->avatar) && file_exists(__DIR__ . '/' . $c->avatar)): ?>
                      <img src="/<?= html_escape($c->avatar) ?>" alt="Avatar" class="rounded-circle" style="width: 45px; height: 45px; object-fit: cover;">
                    <?php else: ?>
                      <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width: 45px; height: 45px; font-weight: bold;">
                        <?= strtoupper(substr($c->name, 0, 1)) ?>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td class="align-middle"><?= html_escape($c->name) ?></td>
                  <td class="align-middle"><?= html_escape($c->phone) ?></td>
                  <td class="align-middle"><?= html_escape(date("d-m-Y", strtotime($c->created_at))) ?></td>
                  <td class="align-middle"><?= html_escape($c->notes) ?></td>
                  <td class="text-center align-middle">
                    <a href="/edit.php?id=<?= $c->id ?>" class="btn btn-xs btn-warning">
                      <i alt="Edit" class="fa fa-pencil"></i> Sửa
                    </a>
                    <form class="d-inline ms-1" action="/delete.php" method="POST">
                      <input type="hidden" name="id" value="<?= $c->id ?>">
                      <button type="submit" class="btn btn-xs btn-danger" name="delete-contact">
                        <i alt="Delete" class="fa fa-trash"></i> Xóa
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center">Chưa có liên hệ nào.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>

        <!-- Section Pagination (Luôn hiển thị) -->
        <nav class="d-flex justify-content-center mt-3">
          <ul class="pagination">
            <li class="page-item <?= $paginator->getPrevPage() ? '' : 'disabled' ?>">
              <a role="button" href="/?page=<?= $paginator->getPrevPage() ? $paginator->getPrevPage() : 1 ?>&limit=<?= $limit ?>" class="page-link">
                <span>&laquo;</span>
              </a>
            </li>
            <?php foreach ($pages as $p): ?>
              <li class="page-item <?= $paginator->currentPage === $p ? 'active' : '' ?>">
                <a role="button" href="/?page=<?= $p ?>&limit=<?= $limit ?>" class="page-link"><?= $p ?></a>
              </li>
            <?php endforeach; ?>
            <li class="page-item <?= $paginator->getNextPage() ? '' : 'disabled' ?>">
              <a role="button" href="/?page=<?= $paginator->getNextPage() ? $paginator->getNextPage() : $paginator->totalPages ?>&limit=<?= $limit ?>" class="page-link">
                <span>&raquo;</span>
              </a>
            </li>
          </ul>
        </nav>

      </div>
    </div>
  </div>

  <!-- Modal xác nhận xóa -->
  <div id="delete-confirm" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Xác nhận xóa</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Bạn có chắc chắn muốn xóa liên hệ này?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
          <button type="button" class="btn btn-danger" id="delete">Xóa</button>
        </div>
      </div>
    </div>
  </div>

  <?php include_once __DIR__ . '/../src/partials/footer.php'; ?>

  <script>
    const deleteButtons = document.querySelectorAll('button[name="delete-contact"]');
    deleteButtons.forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        const form = button.closest('form');
        const nameTd = button.closest('tr').querySelector('td:nth-child(2)');
        if (nameTd) {
          document.querySelector('.modal-body').textContent = `Bạn có chắc muốn xóa "${nameTd.textContent.trim()}"?`;
        }

        const submitForm = function() {
          form.submit();
        };

        document.getElementById('delete').addEventListener('click', submitForm, { once: true });

        const modalEl = document.getElementById('delete-confirm');
        modalEl.addEventListener('hidden.bs.modal', function() {
          document.getElementById('delete').removeEventListener('click', submitForm);
        });

        const confirmModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
        confirmModal.show();
      });
    });
  </script>
</body>
</html>