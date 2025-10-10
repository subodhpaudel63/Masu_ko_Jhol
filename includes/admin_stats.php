<?php
declare(strict_types=1);
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth_check.php';

$auth = getUserFromCookie();
if (!$auth || ($auth['userType'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['error'=>'unauthorized']);
    exit;
}

$today = date('Y-m-d');
$week_ago = date('Y-m-d', strtotime('-7 days'));
$month_ago = date('Y-m-d', strtotime('-30 days'));

// Using mysqli ($conn)
function q1(string $sql, string $types = '', array $params = []) {
    global $conn; $stmt = $conn->prepare($sql); if ($types) { $stmt->bind_param($types, ...$params);} $stmt->execute(); return $stmt->get_result();
}

// Metrics
$resRevenue = q1("SELECT COALESCE(SUM(total_price),0) AS total FROM orders WHERE order_date = ?", 's', [$today])->fetch_assoc();
$resOrders  = q1("SELECT COUNT(*) AS c FROM orders WHERE order_date = ?", 's', [$today])->fetch_assoc();
$resCust    = q1("SELECT COUNT(*) AS c FROM users WHERE user_type='user'")->fetch_assoc();
$resExpense = q1("SELECT ROUND(AVG(total_price),0) AS avgp FROM orders WHERE order_date >= DATE_SUB(?, INTERVAL 7 DAY)", 's', [$today])->fetch_assoc();

// Weekly data for charts
$weeklyRevenue = q1("SELECT DATE(order_date) as date, COALESCE(SUM(total_price),0) AS revenue FROM orders WHERE order_date >= ? GROUP BY DATE(order_date) ORDER BY DATE(order_date)", 's', [$week_ago])->fetch_all(MYSQLI_ASSOC);
$weeklyOrders = q1("SELECT DATE(order_date) as date, COUNT(*) AS orders FROM orders WHERE order_date >= ? GROUP BY DATE(order_date) ORDER BY DATE(order_date)", 's', [$week_ago])->fetch_all(MYSQLI_ASSOC);

// Monthly data
$monthlyRevenue = q1("SELECT DATE_FORMAT(order_date, '%Y-%m') as month, COALESCE(SUM(total_price),0) AS revenue FROM orders WHERE order_date >= ? GROUP BY DATE_FORMAT(order_date, '%Y-%m') ORDER BY DATE_FORMAT(order_date, '%Y-%m')", 's', [$month_ago])->fetch_all(MYSQLI_ASSOC);

// Trending by most ordered items
$trending = q1("SELECT menu_name, menu_price, menu_image AS image, COUNT(*) as qty FROM orders GROUP BY menu_name, menu_price, menu_image ORDER BY qty DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);

// Snapshots
$latestOrders = q1("SELECT order_id, email, menu_name, status, total_price, address, created_at FROM orders ORDER BY created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
$customersList = q1("SELECT id, email, user_type, created_at FROM users ORDER BY created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
$bookings = q1("SELECT id, name, email, booking_date, booking_time, people, status FROM bookings ORDER BY created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
$menu = q1("SELECT menu_id, menu_name, menu_price, menu_image FROM menu ORDER BY created_at DESC LIMIT 12")->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'todayRevenue' => (int)($resRevenue['total'] ?? 0),
    'todayOrders'  => (int)($resOrders['c'] ?? 0),
    'customers'    => (int)($resCust['c'] ?? 0),
    'avgExpense'   => (int)($resExpense['avgp'] ?? 0),
    'weeklyRevenue' => $weeklyRevenue,
    'weeklyOrders' => $weeklyOrders,
    'monthlyRevenue' => $monthlyRevenue,
    'trending'     => $trending,
    'latestOrders' => isset($_GET['snap']) ? $latestOrders : null,
    'customersList'=> isset($_GET['snap']) ? $customersList : null,
    'bookings'     => isset($_GET['snap']) ? $bookings : null,
    'menu'         => isset($_GET['snap']) ? $menu : null,
]);