<?php
// Abstraction: Abstract base class
abstract class EventBase {
    protected $conn; // Encapsulation: only accessible in class and subclasses
    protected $table = 'events';

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Encapsulation: protected query method
    protected function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    // Abstraction: must be implemented by subclasses
    abstract public function getAll();
}

// Inheritance: AdminEventManager inherits EventBase
class AdminEventManager extends EventBase {
    public function getAll() {
        return $this->query("SELECT * FROM {$this->table} ORDER BY date DESC");
    }
    public function deleteEvent($id) {
        return $this->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }
}

// Polymorphism: UserEventManager has different getAll logic
class UserEventManager extends EventBase {
    public function getAll() {
        return $this->query("SELECT * FROM {$this->table} WHERE date >= CURDATE() ORDER BY date ASC");
    }
}

// Example usage in manage_events.php
require_once 'classes/EventManager.php';

$eventManager = ($_SESSION['role'] === 'admin')
    ? new AdminEventManager($conn)
    : new UserEventManager($conn);

$events = [];
$res = $eventManager->getAll();
if($res) {
    while($row = $res->fetch_assoc()) {
        $events[] = $row;
    }
}
?>