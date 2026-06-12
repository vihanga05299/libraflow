<?php
/**
 * includes/dsa.php
 *
 * Data Structures used in this Library System
 * -------------------------------------------
 * 1. QUEUE  (borrow_queue table) — FIFO: first borrow request is first served
 * 2. STACK  (reservation_stack) — LIFO: last reservation can be popped first
 * 3. LINKED LIST (categories)   — next_id pointer chains categories together
 * 4. BINARY SEARCH              — used to search books by title efficiently
 */

class LibraryQueue {
    private $conn;

    public function __construct($conn) { $this->conn = $conn; }

    /** Enqueue: add a borrow record to the back of the queue */
    public function enqueue($book_id, $member_id, $due_date) {
        // Get current max queue_pos for this book
        $res = $this->conn->query(
            "SELECT COALESCE(MAX(queue_pos),0)+1 AS next_pos
               FROM borrow_queue
              WHERE book_id=$book_id AND status='borrowed'"
        );
        $pos = $res->fetch_assoc()['next_pos'];
        $today = date('Y-m-d');
        $this->conn->query(
            "INSERT INTO borrow_queue (book_id,member_id,borrow_date,due_date,queue_pos)
             VALUES ($book_id,$member_id,'$today','$due_date',$pos)"
        );
        // Decrease available copies
        $this->conn->query("UPDATE books SET available=available-1 WHERE id=$book_id AND available>0");
        return $this->conn->insert_id;
    }

    /** Dequeue: mark the front-of-queue item as returned */
    public function dequeue($borrow_id) {
        $res = $this->conn->query(
            "SELECT book_id FROM borrow_queue WHERE id=$borrow_id"
        );
        if ($row = $res->fetch_assoc()) {
            $book_id = $row['book_id'];
            $today   = date('Y-m-d');
            $this->conn->query(
                "UPDATE borrow_queue
                    SET status='returned', return_date='$today'
                  WHERE id=$borrow_id"
            );
            $this->conn->query(
                "UPDATE books SET available=available+1 WHERE id=$book_id"
            );
            return true;
        }
        return false;
    }

    /** Peek: view the front of the queue (oldest unreturned borrow) */
    public function peek($book_id) {
        $res = $this->conn->query(
            "SELECT bq.*, m.name AS member_name
               FROM borrow_queue bq
               JOIN members m ON bq.member_id=m.id
              WHERE bq.book_id=$book_id AND bq.status='borrowed'
           ORDER BY bq.queue_pos ASC LIMIT 1"
        );
        return $res->fetch_assoc();
    }
}

class LibraryStack {
    private $conn;

    public function __construct($conn) { $this->conn = $conn; }

    /** Push: add a reservation on top of the stack */
    public function push($book_id, $member_id) {
        $res = $this->conn->query(
            "SELECT COALESCE(MAX(stack_depth),0)+1 AS depth
               FROM reservation_stack
              WHERE book_id=$book_id AND status='pending'"
        );
        $depth = $res->fetch_assoc()['depth'];
        $this->conn->query(
            "INSERT INTO reservation_stack (book_id,member_id,stack_depth)
             VALUES ($book_id,$member_id,$depth)"
        );
        return $this->conn->insert_id;
    }

    /** Pop: remove the top reservation (highest stack_depth) */
    public function pop($book_id) {
        $res = $this->conn->query(
            "SELECT * FROM reservation_stack
              WHERE book_id=$book_id AND status='pending'
           ORDER BY stack_depth DESC LIMIT 1"
        );
        if ($row = $res->fetch_assoc()) {
            $this->conn->query(
                "UPDATE reservation_stack SET status='fulfilled'
                  WHERE id={$row['id']}"
            );
            return $row;
        }
        return null;
    }

    /** Peek top of stack */
    public function peek($book_id) {
        $res = $this->conn->query(
            "SELECT rs.*, m.name AS member_name
               FROM reservation_stack rs
               JOIN members m ON rs.member_id=m.id
              WHERE rs.book_id=$book_id AND rs.status='pending'
           ORDER BY rs.stack_depth DESC LIMIT 1"
        );
        return $res->fetch_assoc();
    }
}

/**
 * Binary Search on a sorted PHP array of book titles.
 * Returns the index of the matching book or -1.
 */
function binarySearchBooks($books, $target) {
    $low  = 0;
    $high = count($books) - 1;
    $target = strtolower($target);
    while ($low <= $high) {
        $mid  = intdiv($low + $high, 2);
        $cmp  = strcmp(strtolower($books[$mid]['title']), $target);
        if ($cmp === 0) return $mid;
        if ($cmp < 0)  $low  = $mid + 1;
        else           $high = $mid - 1;
    }
    return -1;
}
?>
