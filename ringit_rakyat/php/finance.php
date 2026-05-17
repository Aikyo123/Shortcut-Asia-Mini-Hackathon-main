<?php
require_once 'config.php';
requireLogin();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {

    
    case 'add':
        $type        = $_POST['type'] ?? '';
        $category    = trim($_POST['category'] ?? '');
        $amount      = floatval($_POST['amount'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $date        = $_POST['date'] ?? date('Y-m-d');

        if (!in_array($type, ['income','expense']) || !$category || $amount <= 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid entry data.']);
        }

        $db   = getDB();
        $stmt = $db->prepare("INSERT INTO finance_entries (user_id, type, category, amount, description, entry_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('issdss', $user_id, $type, $category, $amount, $description, $date);

        if ($stmt->execute()) {
            jsonResponse(['success' => true, 'message' => 'Entry added!']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to add entry.']);
        }
        break;

   
    case 'get':
        $month = $_GET['month'] ?? date('Y-m');
        $db    = getDB();
        $stmt  = $db->prepare("SELECT * FROM finance_entries WHERE user_id=? AND DATE_FORMAT(entry_date,'%Y-%m')=? ORDER BY entry_date DESC, created_at DESC");
        $stmt->bind_param('is', $user_id, $month);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        
        $income  = array_sum(array_column(array_filter($rows, fn($r) => $r['type']==='income'), 'amount'));
        $expense = array_sum(array_column(array_filter($rows, fn($r) => $r['type']==='expense'), 'amount'));

        jsonResponse([
            'success' => true,
            'entries' => $rows,
            'summary' => [
                'income'  => $income,
                'expense' => $expense,
                'balance' => $income - $expense
            ]
        ]);
        break;

   
    case 'delete':
        $id   = intval($_POST['id'] ?? 0);
        $db   = getDB();
        $stmt = $db->prepare("DELETE FROM finance_entries WHERE id=? AND user_id=?");
        $stmt->bind_param('ii', $id, $user_id);
        $stmt->execute();
        jsonResponse(['success' => $stmt->affected_rows > 0, 'message' => $stmt->affected_rows > 0 ? 'Deleted!' : 'Not found.']);
        break;

    
    case 'set_goal':
        $goal = floatval($_POST['goal'] ?? 0);
        if ($goal <= 0) jsonResponse(['success' => false, 'message' => 'Invalid goal amount.']);

        $db   = getDB();
        $stmt = $db->prepare("UPDATE users SET daily_goal=? WHERE id=?");
        $stmt->bind_param('di', $goal, $user_id);
        $stmt->execute();
        $_SESSION['daily_goal'] = $goal;

        
        $today = date('Y-m-d');
        $stmt  = $db->prepare("INSERT INTO daily_goals (user_id, goal_amount, goal_date) VALUES (?,?,?) ON DUPLICATE KEY UPDATE goal_amount=?");
        $stmt->bind_param('idsd', $user_id, $goal, $today, $goal);
        $stmt->execute();

        jsonResponse(['success' => true, 'message' => "Daily goal set to RM $goal"]);
        break;

    
    case 'today':
        $today = date('Y-m-d');
        $db    = getDB();

        $stmt  = $db->prepare("SELECT type, SUM(amount) as total FROM finance_entries WHERE user_id=? AND entry_date=? GROUP BY type");
        $stmt->bind_param('is', $user_id, $today);
        $stmt->execute();
        $rows  = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $income = $expense = 0;
        foreach ($rows as $r) {
            if ($r['type'] === 'income')  $income  = $r['total'];
            if ($r['type'] === 'expense') $expense = $r['total'];
        }

        jsonResponse([
            'success' => true,
            'today'   => [
                'income'  => $income,
                'expense' => $expense,
                'net'     => $income - $expense,
                'goal'    => $_SESSION['daily_goal'] ?? 50
            ]
        ]);
        break;

    
    case 'update_profile':
        $name  = trim($_POST['name'] ?? '');
        $goal  = floatval($_POST['daily_goal'] ?? 50);
        $color = trim($_POST['avatar_color'] ?? '#4CAF50');

        if (!$name) jsonResponse(['success' => false, 'message' => 'Name required.']);

        $db   = getDB();
        $stmt = $db->prepare("UPDATE users SET name=?, daily_goal=?, avatar_color=? WHERE id=?");
        $stmt->bind_param('sdsi', $name, $goal, $color, $user_id);
        $stmt->execute();

        $_SESSION['user_name']    = $name;
        $_SESSION['daily_goal']   = $goal;
        $_SESSION['avatar_color'] = $color;

        jsonResponse(['success' => true, 'message' => 'Profile updated!']);
        break;

    default:
        jsonResponse(['error' => 'Unknown action'], 400);
}
?>
