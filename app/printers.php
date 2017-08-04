<?php
// error_reporting(-1);
// ini_set('display_errors', 1);
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
$error = $message = '';
include 'helpers.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["printer_id"])) { $error .= '<p><strong>Printer</strong> is not selected</p>'; }
    if (empty($error) && del_printer($_POST["printer_id"])) {
        $message = 'Printer successfully deleted';
    } else {
        $error .= 'Action Failed!, Please try again';
    }
}
$printers = get_printers();
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
                            <a href="index.php" class="nav-item">
                                PHP POS Print Server
                            </a>
                        </div>
                        <div class="nav-right">
                            <?php if (file_exists('./logs.php')) { ?>
                            <a href="logs.php" class="nav-item">
                                Logs
                            </a>
                            <?php } ?>
                            <a href="printers.php" class="nav-item is-active">
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
                <h1 class="title" style="margin-bottom:0;">List Printers</h1>
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
                <p class="subtitle">Please review the printer or try test printing.</p>

                <table class="table is-bordered is-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Profile</th>
                            <th>Path</th>
                            <th>IP Address</th>
                            <th>Port</th>
                            <th style="width:65px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($printers)) {
                            foreach($printers as $printer) {
                                echo '<tr>';
                                echo '<td>'.$printer->title.'</td>';
                                echo '<td>'.$printer->type.'</td>';
                                echo '<td>'.$printer->profile.'</td>';
                                echo '<td>'.$printer->path.'</td>';
                                echo '<td>'.$printer->ip_address.'</td>';
                                echo '<td>'.$printer->port.'</td>';
                                echo '<td class="has-text-centered"><span class="icon"><a href="#" class="test-print" data-printer-id="'.$printer->id.'"><img src="images/print.png" aslt="print"></a></span> <span class="icon"><form action="printers.php" method="post" accept-charset="utf-8"><input type="hidden" name="printer_id" value="'.$printer->id.'"><input type="image" src="images/trash.png" alt="del" style="height:24px;" /></form></span></td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="7">No Printer has been added, please <a href="add_printer.php">add one</a>.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>

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
    <script type="text/javascript">
        var printers = <?= !empty($printers) ? json_encode($printers) : '{}'; ?>;
    </script>
</body>
</html>
