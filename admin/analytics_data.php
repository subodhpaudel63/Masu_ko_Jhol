<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/admin_auth.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$date_range = $_GET['date_range'] ?? 'week';
$custom_start = $_GET['start_date'] ?? '';
$custom_end = $_GET['end_date'] ?? '';

switch ($date_range) {
    case 'today':
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
        break;
    case 'month':
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $end_date = date('Y-m-d');
        break;
    case 'custom':
        $start_date = $custom_start ?: date('Y-m-d', strtotime('-7 days'));
        $end_date = $custom_end ?: date('Y-m-d');
        break;
    default:
        $start_date = date('Y-m-d', strtotime('-6 days'));
        $end_date = date('Y-m-d');
        break;
}

$period_start = new DateTime($start_date);
$period_days = (int) max(1, $period_start->diff(new DateTime($end_date))->days + 1);

$daily_sales = [];
for ($i = 0; $i < $period_days; $i++) {
    $day = (clone $period_start)->modify("+{$i} day")->format('Y-m-d');
    $daily_sales[$day] = ['date' => $day, 'revenue' => 0.0, 'orders' => 0];
}

$sales_query = $conn->prepare("
    SELECT DATE(order_date) AS date, COUNT(*) AS order_count, COALESCE(SUM(total_price), 0) AS revenue
    FROM orders
    WHERE order_date BETWEEN ? AND ?
    GROUP BY DATE(order_date)
    ORDER BY DATE(order_date)
");
$sales_query->bind_param('ss', $start_date, $end_date);
$sales_query->execute();
$sales_result = $sales_query->get_result();
while ($row = $sales_result->fetch_assoc()) {
    $day = $row['date'];
    if (isset($daily_sales[$day])) {
        $daily_sales[$day]['revenue'] = (float) $row['revenue'];
        $daily_sales[$day]['orders'] = (int) $row['order_count'];
    }
}
$sales_data = array_values($daily_sales);

$totals_query = $conn->prepare("
    SELECT COALESCE(SUM(total_price), 0) AS revenue, COUNT(*) AS orders, COUNT(DISTINCT email) AS customers
    FROM orders
    WHERE order_date BETWEEN ? AND ?
");
$totals_query->bind_param('ss', $start_date, $end_date);
$totals_query->execute();
$totals = $totals_query->get_result()->fetch_assoc() ?: [];

$prev_start = (clone $period_start)->modify("-{$period_days} day")->format('Y-m-d');
$prev_end = (clone $period_start)->modify('-1 day')->format('Y-m-d');
$prev_query = $conn->prepare("SELECT COALESCE(SUM(total_price), 0) AS revenue, COUNT(*) AS orders FROM orders WHERE order_date BETWEEN ? AND ?");
$prev_query->bind_param('ss', $prev_start, $prev_end);
$prev_query->execute();
$prev_totals = $prev_query->get_result()->fetch_assoc() ?: [];

$total_revenue = (float) ($totals['revenue'] ?? 0);
$total_orders = (int) ($totals['orders'] ?? 0);
$avg_order_value = $total_orders > 0 ? $total_revenue / $total_orders : 0.0;
$new_customers = (int) ($totals['customers'] ?? 0);
$prev_revenue = (float) ($prev_totals['revenue'] ?? 0);
$prev_orders = (int) ($prev_totals['orders'] ?? 0);
$prev_avg = $prev_orders > 0 ? $prev_revenue / $prev_orders : 0.0;

$revenue_change = $prev_revenue > 0 ? (($total_revenue - $prev_revenue) / $prev_revenue) * 100 : 0.0;
$orders_change = $prev_orders > 0 ? (($total_orders - $prev_orders) / $prev_orders) * 100 : 0.0;
$avg_change = $prev_avg > 0 ? (($avg_order_value - $prev_avg) / $prev_avg) * 100 : 0.0;

$menu_query = $conn->prepare("
    SELECT menu_name, COUNT(*) AS order_count, COALESCE(SUM(total_price), 0) AS revenue
    FROM orders
    WHERE order_date BETWEEN ? AND ?
    GROUP BY menu_id, menu_name
    ORDER BY revenue DESC, order_count DESC
    LIMIT 5
");
$menu_query->bind_param('ss', $start_date, $end_date);
$menu_query->execute();
$menu_result = $menu_query->get_result();
$top_items = [];
while ($row = $menu_result->fetch_assoc()) {
    $top_items[] = [
        'menu_name' => $row['menu_name'],
        'orders' => (int) $row['order_count'],
        'revenue' => (float) $row['revenue'],
    ];
}

$payment_labels = ['Cash on Delivery', 'Paid Online', 'Restaurant Payment'];
$payment_values = [55, 35, 10];
$payment_total = array_sum($payment_values);
$payment_percentages = array_map(
    static fn (int $value): int => (int) round(($value / max(1, $payment_total)) * 100),
    $payment_values
);

echo json_encode([
    'range' => [
        'start' => $start_date,
        'end' => $end_date,
        'label' => date('d M Y', strtotime($start_date)) . ' - ' . date('d M Y', strtotime($end_date)),
    ],
    'stats' => [
        'totalRevenue' => $total_revenue,
        'totalOrders' => $total_orders,
        'avgOrderValue' => $avg_order_value,
        'newCustomers' => $new_customers,
        'revenueChange' => $revenue_change,
        'ordersChange' => $orders_change,
        'avgChange' => $avg_change,
    ],
    'charts' => [
        'labels' => array_map(static fn ($row) => date('d M', strtotime($row['date'])), $sales_data),
        'revenue' => array_map(static fn ($row) => (float) $row['revenue'], $sales_data),
        'orders' => array_map(static fn ($row) => (int) $row['orders'], $sales_data),
        'paymentLabels' => $payment_labels,
        'paymentValues' => $payment_values,
        'paymentPercentages' => $payment_percentages,
        'paymentTotal' => $payment_total,
    ],
    'topItems' => $top_items,
], JSON_UNESCAPED_UNICODE);
