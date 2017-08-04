<?php
// error_reporting(-1);
// ini_set('display_errors', 1);
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
$error = $message = '';
include 'helpers.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["receipt_printer"])) { $error .= '<p><strong>Receipt printer</strong> is required</p>'; }
    if (empty($_POST["order_printers"])) { $error .= '<p><strong>Order printer(s)</strong> are required</p>'; }

    if (empty($error)) {
        if (update_printers($_POST["receipt_printer"], $_POST["order_printers"])) {
            $message = 'Printers successfully updated';
        } else {
            $error .= 'Action Failed!, Please try again';
        }
    }
}
$printers = get_printers();
$order_printers = get_order_printers();
$receipt_printer = get_receipt_printer();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="images/icon.png"/>

    <title>PHP POS Print Server</title>
    <link rel="stylesheet" href="css/bulma.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="wrapper">
        <section class="hero is-info">
            <div class="hero-head">
                <header class="nav">
                    <div class="container">
                        <div class="nav-left">
                            <a href="index.php" class="nav-item is-active">
                                PHP POS Print Server
                            </a>
                        </div>
                        <div class="nav-right">
                            <?php if (file_exists('./logs.php')) { ?>
                            <a href="logs.php" class="nav-item">
                                Logs
                            </a>
                            <?php } ?>
                            <a href="printers.php" class="nav-item">
                                Printers
                            </a>
                            <a href="add_printer.php" class="nav-item">
                                Add Printer
                            </a>
                        </div>
                    </div>
                </header>
            </div>
            <div class="hero-body">
                <div class="container has-text-centered">
                    <h1 class="title">
                        PHP POS Print Server
                    </h1>
                    <h2 class="subtitle">
                        A php application for printing POS receipts.
                    </h2>
                    <a href="#" class="button is-large status" onclick="return checkStatus()">Checking...</a>
                    <div id="message" style="display:none;"><div id="notification" class="notification"></div></div>
                </div>
            </div>
        </section>

        <div class="hero is-light has-text-centered">
            <div class="hero-body heading">
                <h1 class="title" style="margin-bottom:0;">Default Printers</h1>
            </div>
        </div>

        <section class="section">
            <div class="container">

                <?php
                if ($message) {
                    echo '<div class="notification is-success">'.$message.'</div>';
                }
                ?>
                <?php
                if ($error) {
                    echo '<div class="notification is-danger">'.$error.'</div>';
                }
                ?>
                <p class="subtitle">Please select receipt and order printers to update defaults.</p>

                <form action="index.php" method="post" accept-charset="utf-8">

                    <div class="field is-horizontal">
                        <div class="field-label is-normal">
                            <label class="label" for="receipt_printer">Bill & Receipt Printer</label>
                        </div>
                        <div class="field-body">
                            <div class="field">
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="receipt_printer" id="receipt_printer">
                                            <?php
                                            if (!empty($printers)) {
                                                foreach($printers as $printer) {
                                                    echo '<option value="'.$printer->id.'"'.(($printer->id == $receipt_printer) ? ' selected="selected"' : '').'>'.$printer->title.'</option>';
                                                }
                                            } else {
                                                echo '<option>Please add printer first</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="field is-horizontal">
                        <div class="field-label is-normal">
                            <label class="label" for="order_printers">Order Printers</label>
                        </div>
                        <div class="field-body">
                            <div class="field">
                                <div class="control">
                                    <div class="select multiple is-fullwidth">
                                        <select name="order_printers[]" id="order_printers" multiple>
                                            <?php
                                            if (!empty($printers)) {
                                                foreach($printers as $printer) {
                                                    echo '<option value="'.$printer->id.'"'.(in_array($printer->id, $order_printers) ? ' selected="selected"' : '').'>'.$printer->title.'</option>';
                                                }
                                            } else {
                                                echo '<option>Please add printer first</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="field is-horizontal" style="margin-top:10px;">
                        <div class="field-label"></div>
                        <div class="field-body">
                            <div class="field">
                                <div class="control">
                                    <button type="submit" name="update_printer" class="button is-primary">
                                        Save Changes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <footer class="footer">
        <div class="container">
            <p>
                <span class="icon is-pulled-right">
                    <img src="images/icon.png" alt="">
                </span>
                &copy; <?= date('Y'); ?> @ tecdiary.com
            </p>
        </div>
    </footer>

    <script type="text/javascript" src="js/script.js"></script>
</body>
</html>
