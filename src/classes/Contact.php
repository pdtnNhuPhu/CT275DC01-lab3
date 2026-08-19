<?php
namespace CT275\Labs;

use PDO;

class Contact
{
    private ?PDO $db;

    public int $id = 0;
    public string $name = '';
    public string $phone = '';
    public string $notes = '';
    public ?string $avatar = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    public function __construct(?PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function fill(array $data): Contact
    {
        $this->name = trim($data['name'] ?? '');
        $this->phone = trim($data['phone'] ?? '');
        $this->notes = trim($data['notes'] ?? '');
        if (isset($data['avatar'])) {
            $this->avatar = $data['avatar'];
        }
        return $this;
    }

    public function fillFromDbRow(array $row): Contact
    {
        $this->id = (int)$row['id'];
        $this->name = $row['name'];
        $this->phone = $row['phone'];
        $this->notes = $row['notes'];
        $this->avatar = $row['avatar'] ?? null;
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
        return $this;
    }

    public function validate(array $data): array
    {
        $errors = [];
        if (empty(trim($data['name'] ?? ''))) {
            $errors['name'] = 'Tên không hợp lệ.';
        }
        if (strlen(trim($data['phone'] ?? '')) < 10 || strlen(trim($data['phone'] ?? '')) > 15) {
            $errors['phone'] = 'Số điện thoại phải từ 10 đến 15 ký tự.';
        }
        if (strlen(trim($data['notes'] ?? '')) > 255) {
            $errors['notes'] = 'Ghi chú tối đa 255 ký tự.';
        }
        return $errors;
    }

    public function all(): array
    {
        $contacts = [];
        $statement = $this->db->prepare('SELECT * FROM contacts ORDER BY id DESC');
        $statement->execute();
        while ($row = $statement->fetch()) {
            $contact = new Contact($this->db);
            $contact->fillFromDbRow($row);
            $contacts[] = $contact;
        }
        return $contacts;
    }

    public function count(): int
    {
        $statement = $this->db->prepare('SELECT count(*) FROM contacts');
        $statement->execute();
        return (int)$statement->fetchColumn();
    }

    public function paginate(int $offset = 0, int $limit = 5): array
    {
        $contacts = [];
        $statement = $this->db->prepare('SELECT * FROM contacts ORDER BY id DESC LIMIT :limit OFFSET :offset');
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        while ($row = $statement->fetch()) {
            $contact = new Contact($this->db);
            $contact->fillFromDbRow($row);
            $contacts[] = $contact;
        }
        return $contacts;
    }

    public function find(int $id): ?Contact
    {
        $statement = $this->db->prepare('SELECT * FROM contacts WHERE id = :id');
        $statement->execute(['id' => $id]);
        if ($row = $statement->fetch()) {
            $this->fillFromDbRow($row);
            return $this;
        }
        return null;
    }

    public function save(): bool
    {
        $result = false;
        if ($this->id > 0) {
            $statement = $this->db->prepare(
                'UPDATE contacts SET name = :name, phone = :phone, notes = :notes, avatar = :avatar, updated_at = now() WHERE id = :id'
            );
            $result = $statement->execute([
                'name' => $this->name,
                'phone' => $this->phone,
                'notes' => $this->notes,
                'avatar' => $this->avatar,
                'id' => $this->id
            ]);
        } else {
            $statement = $this->db->prepare(
                'INSERT INTO contacts (name, phone, notes, avatar, created_at, updated_at) VALUES (:name, :phone, :notes, :avatar, now(), now())'
            );
            $result = $statement->execute([
                'name' => $this->name,
                'phone' => $this->phone,
                'notes' => $this->notes,
                'avatar' => $this->avatar
            ]);
            if ($result) {
                $this->id = (int)$this->db->lastInsertId();
            }
        }
        return $result;
    }

    public function delete(): bool
    {
        $statement = $this->db->prepare('DELETE FROM contacts WHERE id = :id');
        return $statement->execute(['id' => $this->id]);
    }
}