<?php

/*
 *  ==============================================================================
 *  Author	: Mian Saleem
 *  Email	: saleem@tecdiary.com
 *  For		: ESC/POS Print Driver for PHP
 *  License	: MIT License
 *  ==============================================================================
 */

namespace Acme\Esc;

use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

class Escpos
{

    public $printer;
    public $char_per_line = 42;

    public function load($printer) {

        if ($printer->type == 'network') {
            $connector = new NetworkPrintConnector($printer->ip_address, $printer->port);
        } elseif ($printer->type == 'linux') {
            $connector = new FilePrintConnector($printer->path);
        } else {
            $connector = new WindowsPrintConnector($printer->path);
        }

        $this->char_per_line = $printer->char_per_line;
        $profile = CapabilityProfile::load($printer->profile);
        $this->printer = new Printer($connector, $profile);

    }

    public function print($data) {

        if (isset($data->logo) && !empty($data->logo)) {
            $file = basename($data->logo);
            copy($data->logo, 'assets/'.$file);
            $filepath = realpath(dirname(__FILE__));
            $folder_path = dirname($filepath);
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $logo = EscposImage::load($folder_path.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$file, false);
            $this->printer->bitImage($logo);
        }

        $this->printer->setJustification(Printer::JUSTIFY_CENTER);
        $this->printer->setEmphasis(true);
        $this->printer->setTextSize(2, 2);
        $this->printer->text($data->text->store_name);
        $this->printer->setEmphasis(false);

        $this->printer->setTextSize(1, 1);
        $this->printer->feed();
        $this->printer->text($data->text->header);
        $this->printer->setJustification(Printer::JUSTIFY_LEFT);
        $this->printer->text($data->text->info);
        $this->printer->text($data->text->items);

        if (isset($data->text->totals) && !empty($data->text->totals)) {
            $this->printer->text($this->drawLine());
            $this->printer->text($data->text->totals);
        }

        if (isset($data->text->payments) && !empty($data->text->payments)) {
            $this->printer->text($this->drawLine());
            $this->printer->text($data->text->payments);
            $this->printer->feed(2);
        }

        if (isset($data->text->footer) && !empty($data->text->footer)) {
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text($data->text->footer);
        }

        $this->printer->feed(2);
        $this->printer->cut();

        if (isset($data->cash_drawer) && !empty($data->cash_drawer)) {
            $this->printer->pulse();
        }

        $this->printer->close();

    }

    public function open_drawer() {

        $this->printer->pulse();
        $this->printer->close();

    }

    function drawLine() {

        $new = '';
        for ($i = 1; $i < $this->char_per_line; $i++) {
            $new .= '-';
        }
        return $new . "\n";

    }


}
