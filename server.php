<?php

require_once __DIR__ . '/vendor/autoload.php';

use Tec\Ppp\Escpos;

try {

    echo '> Starting server...', "\n";

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
                echo '> Trying receipt printer '.$receipt_printer->title, "\n";
                try {
                    $escpos = new Escpos();
                    $escpos->load($receipt_printer);
                    $escpos->open_drawer();
                    echo '> Opened', "\n";
                } catch (Exception $e) {
                    echo '> Error occurred, unable to open cash drawer. ', $e->getMessage(), "\n";
                }

            } else {

                try {
                    $escpos = new Escpos();
                    $escpos->load($rdata->data->printer);
                    $escpos->open_drawer();
                    echo '> Opened', "\n";
                } catch (Exception $e) {
                    echo '> Error occurred, unable to open cash drawer. ', $e->getMessage(), "\n";
                }

            }
            return;

        } elseif ($rdata->type == 'print-img') {

            echo '> Printing img', "\n";
            if(!isset($data->printer) || empty($data->printer)) {

                if(!isset($rdata->data->order) || empty($rdata->data->order)) {
                    $printers = get_printers();
                    $order_printers = get_order_printers ();
                    foreach ($printers as $printer) {
                        if (in_array($printer->id, $order_printers)) {
                            echo '> Trying order printer '.$printer->title, "\n";
                            try {
                                $escpos = new Escpos();
                                $escpos->load($printer);
                                $escpos->printImg($rdata->data->text);
                                echo '> Printied', "\n";
                            } catch (Exception $e) {
                                echo '> Error occurred, unable to print. ', $e->getMessage(), "\n";
                            }
                        }
                    }
                } else {
                    $receipt_printer = get_receipt_printer();
                    echo '> Trying receipt printer '.$receipt_printer->title, "\n";
                    try {
                        $escpos = new Escpos();
                        $escpos->load($receipt_printer);
                        $escpos->printImg($rdata->data->text);
                        echo '> Printied', "\n";
                    } catch (Exception $e) {
                        echo '> Error occurred, unable to print. ', $e->getMessage(), "\n";
                    }
                }

            } else {

                try {
                    $escpos = new Escpos();
                    $escpos->load($data->printer);
                    $escpos->printImg($rdata->data->text);
                    echo '> Printied', "\n";
                } catch (Exception $e) {
                    echo '> Error occurred, unable to print. ', $e->getMessage(), "\n";
                }

            }

        } elseif ($rdata->type == 'print-data') {

            echo '> Printing ', "\n";
            $rdata->data = json_decode($rdata->data);
            if(!isset($rdata->data->printer) || empty($rdata->data->printer)) {

                $receipt_printer = get_receipt_printer();
                echo '> Trying receipt printer '.$receipt_printer->title, "\n";
                try {
                    $escpos = new Escpos();
                    $escpos->load($receipt_printer);
                    $escpos->print_data($rdata->data);
                    echo '> Printied', "\n";
                } catch (Exception $e) {
                    echo '> Error occurred, unable to print. ', $e->getMessage(), "\n";
                }

            } else {

                try {
                    $escpos = new Escpos();
                    $escpos->load($rdata->data->printer);
                    $escpos->printData($rdata->data);
                    echo '> Printied', "\n";
                } catch (Exception $e) {
                    echo '> Error occurred, unable to print. ', $e->getMessage(), "\n";
                }

            }

        } elseif ($rdata->type == 'print-receipt') {

            echo '> Printing ', "\n";
            if(!isset($rdata->data->printer) || empty($rdata->data->printer)) {

                echo '> No printer data received, trying to get local printers', "\n";
                $printers = get_printers();

                if (isset($rdata->data->order) && !empty($rdata->data->order)) {

                    $order_printers = get_order_printers ();
                    foreach ($printers as $printer) {
                        if (in_array($printer->id, $order_printers)) {
                            echo '> Trying order printer '.$printer->title, "\n";
                            try {
                                $escpos = new Escpos();
                                $escpos->load($printer);
                                $escpos->printData($rdata->data);
                                echo '> Printied', "\n";
                            } catch (Exception $e) {
                                echo '> Error occurred, unable to print. ', $e->getMessage(), "\n";
                            }
                        }
                    }

                } else {

                    $receipt_printer = get_receipt_printer();
                    echo '> Trying receipt printer '.$receipt_printer->title, "\n";
                    try {
                        $escpos = new Escpos();
                        $escpos->load($receipt_printer);
                        $escpos->printData($rdata->data);
                        echo '> Printied', "\n";
                    } catch (Exception $e) {
                        echo '> Error occurred, unable to print. ', $e->getMessage(), "\n";
                    }

                }

            } else {

                try {
                    $escpos = new Escpos();
                    $escpos->load($rdata->data->printer);
                    $escpos->printData($rdata->data);
                    echo '> Printied', "\n";
                } catch (Exception $e) {
                    echo '> Error occurred, unable to print. ', $e->getMessage(), "\n";
                }

            }
            return;

        } else {
            echo '> Unkonwn type ', $rdata->type, "\n";
        }

        return;
    });

    $websocket->on('close', function (Hoa\Event\Bucket $bucket) {
        echo '> Disconnected', "\n";
        return;
    });

    try {
        echo '> Server started', "\n";
        $websocket->run();
    } catch (Exception $e) {
        echo '> Error occurred, server stopped. ', $e->getMessage(), "\n";
    }

} catch (Exception $e) {
    echo '> Error: ', $e->getMessage(), "\n";
}

function read_databsae() {
    $path = __DIR__ . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'data.json';
    $file = file_get_contents($path);
    $data = $file ? json_decode($file) : null;
    return empty($data->printers) ? json_decode(['printers' => [], 'order_printers' => [], 'receipt_printer' => ""]) : $data;
}

function get_printers() {
    $data = read_databsae();
    return $data->printers;
}

function get_receipt_printer() {
    $printers = get_printers();
    $receipt_printer = get_receipt_printer_id();
    foreach ($printers as $printer) {
        if ($printer->id == $receipt_printer) {
            return $printer;
        }
    }
}

function get_receipt_printer_id() {
    $data = read_databsae();
    return !empty($data->receipt_printer) ? $data->receipt_printer : '';
}

function get_order_printers() {
    $data = read_databsae();
    return empty($data->order_printers) ? [] : $data->order_printers;
}
