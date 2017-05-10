<?php

function read_databsae() {
    $file = file_get_contents('../database/data.json');
    $data = $file ? json_decode($file) : null;
    return empty($data->printers) ? json_decode(['printers' => [], 'order_printers' => [], 'receipt_printer' => ""]) : $data;
}

function write_databsae($data) {
    return file_put_contents('../database/data.json', json_encode($data, JSON_PRETTY_PRINT));
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

function add_printer($printer) {
    $data = read_databsae();
    $printers = $data->printers;
    if (!empty($printers)) {
        array_push($printers, $printer);
    } else {
        $printers = [$printer];
    }
    $data->printers = $printers;
    return write_databsae($data);
}

function del_printer($id) {
    $data = read_databsae();
    $printers = [];
    foreach($data->printers as $printer) {
        if ($printer->id != $id) {
            $printers[] = $printer;
        }
    };
    $data->printers = $printers;
    return write_databsae($data);
}

function update_printers($receipt_printer, $order_printers) {
    $data = read_databsae();
    $data->receipt_printer = $receipt_printer;
    $data->order_printers = $order_printers;
    return write_databsae($data);
}

function check_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
