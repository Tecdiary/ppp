<?php

require_once __DIR__ . '/vendor/autoload.php';

use Acme\Esc\Escpos;

$websocket = new Hoa\Websocket\Server(
    new Hoa\Socket\Server('ws://127.0.0.1:6441')
);

$websocket->on('open', function (Hoa\Event\Bucket $bucket) {
    echo '> Connected', "\n";
    return;
});

$websocket->on('message', function (Hoa\Event\Bucket $bucket) {
    $data = $bucket->getData();
    $rdata = json_decode($data['message']);
    echo '> Received request ', $data['message'], "\n";

    if ($rdata->type == 'check-status') {

        $bucket->getSource()->send('Server is running at <br><span>ws://localhost:4661</span>');
        return;

    } elseif ($rdata->type == 'open-cashdrawer') {

        echo '> Opening cash drawer ', "\n";

        if(!isset($rdata->data->printer) || empty($rdata->data->printer)) {

            $receipt_printer = get_receipt_printer();
            foreach ($printers as $printer) {
                if ($printer->id == $receipt_printer) {
                    echo '> Found receipt printer '.$printer->title, "\n";
                    try {
                        $escpos = new Escpos();
                        $escpos->load($printer);
                        $escpos->open_drawer();
                        echo '> Opened', "\n";
                    } catch (Exception $e) {
                        echo '> Error occurred, unable to open cash drawer', $e->getMessage(), "\n";
                    }
                }
            }

        } else {

            try {
                $escpos = new Escpos();
                $escpos->load($rdata->data->printer);
                $escpos->open_drawer();
                echo '> Opened', "\n";
            } catch (Exception $e) {
                echo '> Error occurred, unable to open cash drawer', $e->getMessage(), "\n";
            }

        }
        return;

    } elseif ($rdata->type == 'print-receipt') {

        echo '> Printing ', "\n";
        if(!isset($rdata->data->printer) || empty($rdata->data->printer)) {

            echo '> No printer data received, trying local database', "\n";
            $printers = get_printers();

            if (isset($rdata->data->order) && !empty($rdata->data->order)) {

                $order_printers = get_order_printers ();
                foreach ($printers as $printer) {
                    if (in_array($printer->id, $order_printers)) {
                        echo '> Found order printer '.$printer->title, "\n";
                        try {
                            $escpos = new Escpos();
                            $escpos->load($printer);
                            $escpos->print($rdata->data);
                            echo '> Printied', "\n";
                        } catch (Exception $e) {
                            echo '> Error occurred, unable to print', "\n", $e->getMessage(), "\n";
                        }
                    }
                }

            } else {

                $receipt_printer = get_receipt_printer();
                foreach ($printers as $printer) {
                    if ($printer->id == $receipt_printer) {
                        echo '> Found receipt printer '.$printer->title, "\n";
                        try {
                            $escpos = new Escpos();
                            $escpos->load($printer);
                            $escpos->print($rdata->data);
                            echo '> Printied', "\n";
                        } catch (Exception $e) {
                            echo '> Error occurred, unable to print', "\n", $e->getMessage(), "\n";
                        }
                    }
                }

            }

        } else {

            try {
                $escpos = new Escpos();
                $escpos->load($rdata->data->printer);
                $escpos->print($rdata->data);
                echo '> Printied', "\n";
            } catch (Exception $e) {
                echo '> Error occurred, unable to print', "\n", $e->getMessage(), "\n";
            }

        }
        return;

    } else {
        echo '> Unkonwn type ', $rdata->type, "\n";
    }

    // $bucket->getSource()->send('some message.');
    return;
});

$websocket->on('close', function (Hoa\Event\Bucket $bucket) {
    echo '> Disconnected', "\n";
    return;
});

$websocket->run();

function read_databsae() {
    $file = file_get_contents('database/data.json');
    $data = $file ? json_decode($file) : null;
    return empty($data->printers) ? json_decode(['printers' => [], 'order_printers' => [], 'receipt_printer' => ""]) : $data;
}

function get_printers() {
    $data = read_databsae();
    return $data->printers;
}

function get_receipt_printer() {
    $data = read_databsae();
    return !empty($data->receipt_printer) ? $data->receipt_printer : '';
}

function get_order_printers() {
    $data = read_databsae();
    return empty($data->order_printers) ? [] : $data->order_printers;
}
