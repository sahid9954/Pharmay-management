<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database configuration
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "pharmacy";

    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $customers_name = $_POST['customers_name'];
        $customers_contact_number = $_POST['customers_contact_number'];
        $invoice_number = $_POST['invoice_number'];
        $invoice_date = $_POST['invoice_date'];
        $payment_type = $_POST['payment_type'];

        // Insert or fetch CUSTOMER_ID based on customer details
        $stmt = $pdo->prepare("INSERT INTO customers (CUSTOMER_NAME, CONTACT_NUMBER) VALUES (:customers_name, :customers_contact_number) ON DUPLICATE KEY UPDATE CUSTOMER_ID=LAST_INSERT_ID(CUSTOMER_ID)");
        $stmt->bindParam(':customers_name', $customers_name);
        $stmt->bindParam(':customers_contact_number', $customers_contact_number);
        $stmt->execute();
        $customer_id = $pdo->lastInsertId();

        foreach ($_POST['medicine_name'] as $index => $medicine_name) {
            $stmt = $pdo->prepare("INSERT INTO sales (CUSTOMER_ID, MEDICINE_NAME, EXPIRY_DATE, QUANTITY, MRP, DISCOUNT, TOTAL, INVOICE_NUMBER, PAYMENT_TYPE, INVOICE_DATE)
                                    VALUES (:customer_id, :medicine_name, :expiry_date, :quantity, :mrp, :discount, :total, :invoice_number, :payment_type, :invoice_date)");
            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->bindParam(':medicine_name', $medicine_name);
            $stmt->bindParam(':expiry_date', $_POST['expiry_date'][$index]);
            $stmt->bindParam(':quantity', $_POST['quantity'][$index]);
            $stmt->bindParam(':mrp', $_POST['mrp'][$index]);
            $stmt->bindParam(':discount', $_POST['discount'][$index]);
            $stmt->bindParam(':total', $_POST['total'][$index]);
            $stmt->bindParam(':invoice_number', $invoice_number);
            $stmt->bindParam(':payment_type', $payment_type);
            $stmt->bindParam(':invoice_date', $invoice_date);
            $stmt->execute();
        }

        $success_message = "Sale added successfully!";
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>New Invoice</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <script src="bootstrap/js/jquery.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <link rel="shortcut icon" href="images/icon.svg" type="image/x-icon">
    <link rel="stylesheet" href="css/sidenav.css">
    <link rel="stylesheet" href="css/home.css">
    <script src="js/suggestions.js"></script>
    <script src="js/add_new_invoice.js"></script>
    <script src="js/manage_invoice.js"></script>
    <script src="js/validateForm.js"></script>
    <script src="js/restrict.js"></script>
</head>
<body>
    <!-- Add New Customer Modal -->
    <div id="add_new_customer_model" style="display:none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #ff5252; color: white">
                    <div class="font-weight-bold">Add New Customer</div>
                    <button class="close" style="outline: none;" onclick="document.getElementById('add_new_customer_model').style.display = 'none';"><i class="fa fa-close"></i></button>
                </div>
                <div class="modal-body">
                    <?php include('sections/add_new_customer.html'); ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Including Side Navigations -->
    <?php include("sections/sidenav.html"); ?>

    <div class="container-fluid">
        <div class="container">
            <!-- Header Section -->
            <?php
            require "php/header.php";
            createHeader('clipboard', 'New Invoice', 'Create New Invoice');
            ?>
            <!-- Header Section End -->

            <!-- Form Content -->
            <form method="post" action="">
                <div class="row">
                    <!-- Customer Details -->
                    <div class="row col-md-12">
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold" for="customers_name">Customer Name:</label>
                            <input id="customers_name" type="text" class="form-control" placeholder="Customer Name" name="customers_name" onkeyup="showSuggestions(this.value, 'customer');">
                            <code class="text-danger small font-weight-bold float-right" id="customer_name_error" style="display: none;"></code>
                            <div id="customer_suggestions" class="list-group position-fixed" style="z-index: 1; width: 18.30%; overflow: auto; max-height: 200px;"></div>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold" for="customers_address">Address:</label>
                            <input id="customers_address" type="text" class="form-control" name="customers_address" placeholder="Address" disabled>
                        </div>
                        <div class="col-md-2 form-group">
                            <label class="font-weight-bold" for="invoice_number">Invoice Number:</label>
                            <input id="invoice_number" type="text" class="form-control" name="invoice_number" placeholder="Invoice Number" disabled>
                        </div>
                        <div class="col-md-2 form-group">
                            <label class="font-weight-bold" for="payment_type">Payment Type:</label>
                            <select id="payment_type" class="form-control" name="payment_type">
                                <option value="1">Cash Payment</option>
                                <option value="2">Card Payment</option>
                                <option value="3">Net Banking</option>
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label class="font-weight-bold" for="invoice_date">Date:</label>
                            <input type="date" class="form-control" id="invoice_date" name="invoice_date" value='<?php echo date('Y-m-d'); ?>' onblur="checkDate(this.value, 'date_error');">
                            <code class="text-danger small font-weight-bold float-right" id="date_error" style="display: none;"></code>
                        </div>
                    </div>
                    <!-- Customer Details End -->

                    <!-- New Customer Button -->
                    <div class="row col-md-12">
                        <div class="col-md-2 form-group">
                            <button class="btn btn-primary form-control" onclick="document.getElementById('add_new_customer_model').style.display = 'block';">New Customer</button>
                        </div>
                        <div class="col-md-1 form-group"></div>
                        <div class="col-md-2 form-group">
                            <label class="font-weight-bold" for="customers_contact_number">Contact Number:</label>
                            <input id="customers_contact_number" type="number" class="form-control" name="customers_contact_number" placeholder="Contact Number" disabled>
                        </div>
                    </div>
                    <!-- New Customer Button End -->

                    <div class="col-md-12">
                        <hr class="col-md-12" style="padding: 0; border-top: 3px solid #02b6ff;">
                    </div>

                    <!-- Add Medicines -->
                    <div class="row col-md-12">
                        <div class="row col-md-12 font-weight-bold">
                            <div class="col-md-2">Medicine Name</div>
                            <div class="col-md-2">Batch ID</div>
                            <div class="col-md-1">Ava.Qty.</div>
                            <div class="col-md-1">Expiry</div>
                            <div class="col-md-1">Quantity</div>
                            <div class="col-md-1">MRP</div>
                            <div class="col-md-1">Discount(%)</div>
                            <div class="col-md-1">Total</div>
                            <div class="col-md-2">Action</div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <hr class="col-md-12" style="padding: 0; border-top: 2px solid #02b6ff;">
                    </div>

                    <!-- Medicine Input Rows -->
                    <div id="medicine_rows">
                        <div class="row col-md-12">
                            <div class="col-md-2 form-group">
                                <input type="text" class="form-control" name="medicine_name[]" placeholder="Medicine Name">
                            </div>
                            <div class="col-md-2 form-group">
                                <input type="text" class="form-control" name="batch_id[]" placeholder="Batch ID">
                            </div>
                            <div class="col-md-1 form-group">
                                <input type="text" class="form-control" name="available_quantity[]" placeholder="Available Qty" readonly>
                            </div>
                            <div class="col-md-1 form-group">
                                <input type="date" class="form-control" name="expiry_date[]">
                            </div>
                            <div class="col-md-1 form-group">
                                <input type="number" class="form-control" name="quantity[]" placeholder="Quantity">
                            </div>
                            <div class="col-md-1 form-group">
                                <input type="text" class="form-control" name="mrp[]" placeholder="MRP">
                            </div>
                            <div class="col-md-1 form-group">
                                <input type="number" class="form-control" name="discount[]" placeholder="Discount">
                            </div>
                            <div class="col-md-1 form-group">
                                <input type="text" class="form-control" name="total[]" placeholder="Total">
                            </div>
                            <div class="col-md-2 form-group">
                                <button type="button" class="btn btn-danger form-control" onclick="removeRow(this)">Remove</button>
                            </div>
                        </div>
                    </div>

                    <div class="row col-md-12">
                        <div class="col-md-2 form-group">
                            <button type="button" class="btn btn-primary form-control" onclick="addMedicineRow()">Add Medicine</button>
                        </div>
                        <div class="col-md-10 form-group"></div>
                    </div>

                    <!-- Submit Button -->
                    <div class="row col-md-12">
                        <div class="col-md-12 form-group">
                            <button type="submit" class="btn btn-success form-control">Submit Invoice</button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Success/Error Messages -->
            <?php if (isset($success_message)) : ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php elseif (isset($error_message)) : ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- JavaScript to Handle Form -->
            <script>
                function addMedicineRow() {
                    var rowHtml = `
                        <div class="row col-md-12">
                            <div class="col-md-2 form-group">
                                <input type="text" class="form-control" name="medicine_name[]" placeholder="Medicine Name">
                            </div>
                            <div class="col-md-2 form-group">
                                <input type="text" class="form-control" name="batch_id[]" placeholder="Batch ID">
                            </div>
                            <div class="col-md-1 form-group">
                                <input type="text" class="form-control" name="available_quantity[]" placeholder="Available Qty" readonly>
                            </div>
                            <div class="col-md-1 form-group">
                                <input type="date" class="form-control" name="expiry_date[]">
                            </div>
                            <div class="col-md-1 form-group">
                                <input type="number" class="form-control" name="quantity[]" placeholder="Quantity">
                            </div>
                            <div class="col-md-1 form-group">
                                <input type="text" class="form-control" name="mrp[]" placeholder="MRP">
                            </div>
                            <div class="col-md-1 form-group">
                                <input type="number" class="form-control" name="discount[]" placeholder="Discount">
                            </div>
                            <div class="col-md-1 form-group">
                                <input type="text" class="form-control" name="total[]" placeholder="Total">
                            </div>
                            <div class="col-md-2 form-group">
                                <button type="button" class="btn btn-danger form-control" onclick="removeRow(this)">Remove</button>
                            </div>
                        </div>
                    `;
                    document.getElementById('medicine_rows').insertAdjacentHTML('beforeend', rowHtml);
                }

                function removeRow(button) {
                    button.closest('.row').remove();
                }
            </script>
        </div>
    </div>
</body>
</html>
