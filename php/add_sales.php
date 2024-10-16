<?php
// add_sales.php

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pharmacy";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $customers_name = $_GET['customers_name'];
        $customers_contact_number = $_GET['customers_contact_number'];
        $invoice_number = $_GET['invoice_number'];

        // Insert customer data
        // Note: Handling multiple medicines might require looping and additional SQL queries
        foreach ($_GET['medicine_name'] as $index => $medicine_name) {
            $stmt = $pdo->prepare("INSERT INTO sales (CUSTOMER_ID, MEDICINE_NAME, EXPIRY_DATE, QUANTITY, MRP, DISCOUNT, TOTAL, INVOICE_NUMBER)
                                    VALUES ((SELECT CUSTOMER_ID FROM customers WHERE CUSTOMER_NAME = :customers_name AND CONTACT_NUMBER = :customers_contact_number),
                                            :medicine_name,
                                            :expiry_date,
                                            :quantity,
                                            :mrp,
                                            :discount,
                                            :total,
                                            :invoice_number)");
            $stmt->bindParam(':customers_name', $customers_name);
            $stmt->bindParam(':customers_contact_number', $customers_contact_number);
            $stmt->bindParam(':medicine_name', $medicine_name);
            $stmt->bindParam(':expiry_date', $_GET['expiry_date'][$index]);
            $stmt->bindParam(':quantity', $_GET['quantity'][$index]);
            $stmt->bindParam(':mrp', $_GET['mrp'][$index]);
            $stmt->bindParam(':discount', $_GET['discount'][$index]);
            $stmt->bindParam(':total', $_GET['total'][$index]);
            $stmt->bindParam(':invoice_number', $invoice_number);
            $stmt->execute();
        }

        echo "Sale added successfully!";
    } else {
        echo "Invalid request method!";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$pdo = null;
?>
