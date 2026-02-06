<?php
require_once '../includes/admin_auth.php';
require_admin();
require_once '../includes/db.php';

// Get date range parameters
$date_range = $_GET['date_range'] ?? 'week';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Set date range
switch($date_range) {
    case 'today':
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
        break;
    case 'week':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $end_date = date('Y-m-d');
        break;
    case 'month':
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $end_date = date('Y-m-d');
        break;
    case 'custom':
        $start_date = $start_date ?: date('Y-m-d', strtotime('-7 days'));
        $end_date = $end_date ?: date('Y-m-d');
        break;
    default:
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $end_date = date('Y-m-d');
}

// Fetch data for export
$sales_data = [];
$menu_stats = [];
$booking_stats = [];
$customer_stats = [];

// Sales data
$sales_query = $conn->prepare("
    SELECT DATE(order_date) as date, 
           COUNT(*) as order_count, 
           SUM(total_price) as revenue
    FROM orders 
    WHERE order_date BETWEEN ? AND ?
    GROUP BY DATE(order_date)
    ORDER BY DATE(order_date)
");
$sales_query->bind_param("ss", $start_date, $end_date);
$sales_query->execute();
$sales_result = $sales_query->get_result();
while($row = $sales_result->fetch_assoc()) {
    $sales_data[] = $row;
}

// Menu analytics
$menu_query = $conn->prepare("
    SELECT menu_name, 
           COUNT(*) as order_count, 
           SUM(quantity) as total_quantity,
           SUM(total_price) as revenue
    FROM orders 
    WHERE order_date BETWEEN ? AND ?
    GROUP BY menu_id, menu_name
    ORDER BY order_count DESC
");
$menu_query->bind_param("ss", $start_date, $end_date);
$menu_query->execute();
$menu_result = $menu_query->get_result();
while($row = $menu_result->fetch_assoc()) {
    $menu_stats[] = $row;
}

// Booking statistics
$booking_query = $conn->prepare("
    SELECT status, COUNT(*) as count
    FROM bookings 
    WHERE booking_date BETWEEN ? AND ?
    GROUP BY status
");
$booking_query->bind_param("ss", $start_date, $end_date);
$booking_query->execute();
$booking_result = $booking_query->get_result();
while($row = $booking_result->fetch_assoc()) {
    $booking_stats[] = $row;
}

// Customer analytics
$customer_query = $conn->prepare("
    SELECT email, COUNT(*) as order_count
    FROM orders 
    WHERE order_date BETWEEN ? AND ?
    GROUP BY email
    ORDER BY order_count DESC
");
$customer_query->bind_param("ss", $start_date, $end_date);
$customer_query->execute();
$customer_result = $customer_query->get_result();
while($row = $customer_result->fetch_assoc()) {
    $customer_stats[] = $row;
}

// Generate CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="analytics_report_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// Write headers
fputcsv($output, ['Analytics Report - Masu Ko Jhol']);
fputcsv($output, ['Date Range:', $start_date . ' to ' . $end_date]);
fputcsv($output, ['Generated on:', date('Y-m-d H:i:s')]);
fputcsv($output, []);

// Sales Data
fputcsv($output, ['Sales Trends']);
fputcsv($output, ['Date', 'Orders', 'Revenue (Rs)']);
foreach($sales_data as $row) {
    fputcsv($output, [$row['date'], $row['order_count'], $row['revenue']]);
}
fputcsv($output, []);

// Menu Analytics
fputcsv($output, ['Menu Performance']);
fputcsv($output, ['Item Name', 'Orders', 'Quantity', 'Revenue (Rs)']);
foreach($menu_stats as $row) {
    fputcsv($output, [$row['menu_name'], $row['order_count'], $row['total_quantity'], $row['revenue']]);
}
fputcsv($output, []);

// Booking Statistics
fputcsv($output, ['Booking Statistics']);
fputcsv($output, ['Status', 'Count']);
foreach($booking_stats as $row) {
    fputcsv($output, [$row['status'], $row['count']]);
}
fputcsv($output, []);

// Customer Analytics
fputcsv($output, ['Customer Analytics']);
fputcsv($output, ['Email', 'Order Count', 'Customer Type']);
foreach($customer_stats as $row) {
    $customer_type = $row['order_count'] > 1 ? 'Returning' : 'New';
    fputcsv($output, [$row['email'], $row['order_count'], $customer_type]);
}

fclose($output);