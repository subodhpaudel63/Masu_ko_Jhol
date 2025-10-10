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

// Get period from request (default to 30 days)
$days = intval($_GET['days'] ?? 30);
if ($days <= 0 || $days > 365) $days = 30;

$date_from = date('Y-m-d', strtotime("-$days days"));

// Using mysqli ($conn)
function q1(string $sql, string $types = '', array $params = []) {
  global $conn; $stmt = $conn->prepare($sql); if ($types) { $stmt->bind_param($types, ...$params);} $stmt->execute(); return $stmt->get_result();
}

// Revenue data
$revenue = q1("SELECT DATE(order_date) as date, COALESCE(SUM(total_price),0) AS amount FROM orders WHERE order_date >= ? GROUP BY DATE(order_date) ORDER BY DATE(order_date)", 's', [$date_from])->fetch_all(MYSQLI_ASSOC);

// Orders data
$orders = q1("SELECT DATE(order_date) as date, COUNT(*) AS count FROM orders WHERE order_date >= ? GROUP BY DATE(order_date) ORDER BY DATE(order_date)", 's', [$date_from])->fetch_all(MYSQLI_ASSOC);

// Top selling items
$topItems = q1("SELECT menu_name as name, COUNT(*) as count FROM orders WHERE order_date >= ? GROUP BY menu_name ORDER BY count DESC LIMIT 10", 's', [$date_from])->fetch_all(MYSQLI_ASSOC);

// Sales by category
$categories = q1("SELECT m.menu_category as category, COALESCE(SUM(o.total_price),0) as sales FROM orders o JOIN menu m ON o.menu_id = m.menu_id WHERE o.order_date >= ? GROUP BY m.menu_category ORDER BY sales DESC", 's', [$date_from])->fetch_all(MYSQLI_ASSOC);

echo json_encode([
  'revenue' => $revenue,
  'orders' => $orders,
  'topItems' => $topItems,
  'categories' => $categories
]);