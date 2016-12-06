<?php

require_once __DIR__ . '/vendor/autoload.php';

use Acme\Esc\Escpos;

$websocket = new Hoa\Websocket\Server(
    new Hoa\Socket\Server('ws://127.0.0.1:6441')
);

$websocket->on('open', function (Hoa\Event\Bucket $bucket) {
    echo 'New connection', "\n";
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
        echo '> opening cash drawer ', "\n";
        try {
            $escpos = new Escpos();
            $escpos->load($rdata->data->printer);
            $escpos->open_drawer();
            echo '< opened', "\n";
        } catch (Exception $e) {
            echo '< error occurred, unable to open cash drawer', $e->getMessage(), "\n";
        }
        return;
    } elseif ($rdata->type == 'print-receipt') {
        echo '> printing receipt ', "\n";
        try {
            $escpos = new Escpos();
            $escpos->load($rdata->data->printer);
            $escpos->print($rdata->data);
            echo '< printied', "\n";
        } catch (Exception $e) {
            echo '< error occurred, unable to print', $e->getMessage(), "\n";
        }
        return;
    } else {
        echo '< unkonwn type ', $rdata->type, "\n";
    }

    // $bucket->getSource()->send('some message.');
    return;
});

$websocket->on('close', function (Hoa\Event\Bucket $bucket) {
    echo 'Connection closed', "\n";
    return;
});

$websocket->run();
