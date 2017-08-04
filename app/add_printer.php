<?php
// error_reporting(-1);
// ini_set('display_errors', 1);
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
$error = $message = $title = $type = $profile = $char_per_line = $path = $ip_address = $port = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST["title"])) { $error .= '<p><strong>Title</strong> is required</p>'; }
    if (empty($_POST["type"])) { $error .= '<p><strong>Type</strong> is required</p>'; }
    if (empty($_POST["profile"])) { $error .= '<p><strong>Profile</strong> is required</p>'; }
    if (empty($_POST["char_per_line"])) { $error .= '<p><strong>Characters per line</strong> is required</p>'; }

    if ($_POST["type"] == 'network') {
        if (empty($_POST["ip_address"])) { $error .= '<p><strong>IP Address</strong> is required</p>'; }
        if (empty($_POST["port"])) { $error .= '<p><strong>Port</strong> is required</p>'; }
    }
    if ($_POST["type"] != 'network') {
        if (empty($_POST["path"])) { $error .= '<p><strong>Path</strong> is required</p>'; }
    }

    include 'helpers.php';

    $title = check_input($_POST["title"]);
    $type = check_input($_POST["type"]);
    $profile = check_input($_POST["profile"]);
    $char_per_line = check_input($_POST["char_per_line"]);
    $path = ($_POST["type"] != 'network') ? check_input($_POST["path"]) : '';
    $ip_address = ($_POST["type"] == 'network') ? check_input($_POST["ip_address"]) : '';
    $port = ($_POST["type"] == 'network') ? check_input($_POST["port"]) : '';

    if (!$error) {
        $printer = ['id' => md5(microtime()), 'title' => $title, 'type' => $type, 'profile' => $profile, 'char_per_line' => $char_per_line, 'char_per_line' => $char_per_line, 'path' => $path, 'ip_address' => $ip_address, 'port' => $port];
        if (add_printer($printer)) {
            $error = $title = $type = $profile = $char_per_line = $path = $ip_address = $port = '';
            $message = "Printer successfully added";
        } else {
            $error = 'Action Failed! Please try again';
        }
    }
}

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
                            <a href="printers.php" class="nav-item">
                                Printers
                            </a>
                            <a href="add_printer.php" class="nav-item is-active">
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
                <h1 class="title" style="margin-bottom:0;">Add New Printer</h1>
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
                <p class="subtitle">Please fill the from below to add printer.</p>

                <form action="add_printer.php" method="post" accept-charset="utf-8">

                    <div class="field is-horizontal">
                        <div class="field-label is-normal">
                            <label class="label" for="title">Title</label>
                        </div>
                        <div class="field-body">
                            <div class="field">
                                <div class="control">
                                    <input type="text" name="title" value="<?= $title; ?>" class="input" id="title">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="field is-horizontal">
                        <div class="field-label is-normal">
                            <label class="label" for="type">Type</label>
                        </div>
                        <div class="field-body">
                            <div class="field">
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="type" id="type">
                                            <option value="network"<?= $type == 'network' ? ' selected="selected"' : ''; ?>>Network</option>
                                            <option value="windows"<?= $type == 'windows' ? ' selected="selected"' : ''; ?>>Windows</option>
                                            <option value="linux"<?= $type == 'linux' ? ' selected="selected"' : ''; ?>>Linux</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="field is-horizontal">
                        <div class="field-label is-normal">
                            <label class="label" for="profile">Profile</label>
                        </div>
                        <div class="field-body">
                            <div class="field">
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="profile" id="profile">
                                            <option value="default"<?= $profile == 'default' ? ' selected="selected"' : ''; ?>>Default</option>
                                            <option value="simple"<?= $profile == 'simple' ? ' selected="selected"' : ''; ?>>Simple</option>
                                            <option value="SP2000"<?= $profile == 'SP2000' ? ' selected="selected"' : ''; ?>>Star-branded</option>
                                            <option value="TEP-200M"<?= $profile == 'TEP-200M' ? ' selected="selected"' : ''; ?>>Espon Tep</option>
                                            <option value="P822D"<?= $profile == 'P822D' ? ' selected="selected"' : ''; ?>>P822D</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="field is-horizontal">
                        <div class="field-label is-normal">
                            <label class="label" for="char_per_line">Characters per line</label>
                        </div>
                        <div class="field-body">
                            <div class="field">
                                <div class="control">
                                    <input type="text" name="char_per_line" value="<?= $char_per_line; ?>" class="input" id="char_per_line">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="path">
                        <div class="field is-horizontal">
                            <div class="field-label is-normal">
                                <label class="label" for="path">Path</label>
                            </div>
                            <div class="field-body">
                                <div class="field">
                                    <div class="control">
                                        <input type="text" name="path" value="<?= $path; ?>" class="input" id="path">
                                    </div>
                                    <span class="help">
                                        <strong>For Windows:</strong> (Local USB, Serial or Parallel Printer): Share the printer and enter the share name for your printer here or for Server Message Block (SMB): enter as a smb:// url format such as <code>smb://computername/Receipt Printer</code><br><strong>For Linux:</strong> Parallel as <code>/dev/lp0</code>, USB as <code>/dev/usb/lp1</code>, USB-Serial as <code>/dev/ttyUSB0</code>, Serial as <code>/dev/ttyS0</code><br>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="network">
                        <div class="field is-horizontal">
                            <div class="field-label is-normal">
                                <label class="label" for="ip_address">IP Address</label>
                            </div>
                            <div class="field-body">
                                <div class="field">
                                    <div class="control">
                                        <input type="text" name="ip_address" value="<?= $ip_address; ?>" class="input" id="ip_address">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="field is-horizontal">
                            <div class="field-label is-normal">
                                <label class="label" for="port">Port</label>
                            </div>
                            <div class="field-body">
                                <div class="field">
                                    <div class="control">
                                        <input type="text" name="port" value="<?= !empty($port) ? $port : 9100; ?>" class="input" id="port">
                                    </div>
                                    <span class="help">Most printers are open on port <strong>9100</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="field is-horizontal" style="margin-top:10px;">
                        <div class="field-label"></div>
                        <div class="field-body">
                            <div class="field">
                                <div class="control">
                                    <button type="submit" name="add_printer" class="button is-primary">
                                        Add Printer
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
