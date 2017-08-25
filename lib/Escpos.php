<?php

/*
=============================================================================
*  Author	: Mian Saleem
*  Email	: saleem@tecdiary.com
*  For		: ESC/POS Print Driver for PHP
*  License	: MIT License
*  ==========================================================================
*/

namespace Tec\Ppp;

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
			set_time_limit(30);
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
	
	function printImg($data) {
		
		$this->printer->setJustification(Printer::JUSTIFY_CENTER);
        $file_path = realpath(dirname(__FILE__));
        $folder_path = dirname($file_path);
        $file = date('Y-m-d-H-i-s-').uniqid().'.png';
        $filename = $folder_path.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.$file;
        $imgData = str_replace('data:image/png;base64,', '', $data);
        $imgData = str_replace(' ', '+', $imgData);
        $imgData = base64_decode($imgData);
        file_put_contents($filename, $imgData);
        $img = EscposImage::load($filename, false);
        $this->printer->bitImageColumnFormat($img);
        $this->printer->feed(2);
        $this->printer->cut();
		if (isset($data->cash_drawer) && !empty($data->cash_drawer)) {
			$this->printer->pulse();
		}
        $this->printer->close();
        
    }
    
	function print_data($data) {
		
		$this->printer->setJustification(Printer::JUSTIFY_CENTER);
		if (isset($data->logo) && !empty($data->logo)) {
			$file = basename($data->logo);
			$file_path = realpath(dirname(__FILE__));
			$folder_path = dirname($file_path);
			$this->printer->setJustification(Printer::JUSTIFY_CENTER);
			if (!file_exists($folder_path.DIRECTORY_SEPARATOR.'logos'.DIRECTORY_SEPARATOR.$file)) {
				copy($data->logo, $folder_path.DIRECTORY_SEPARATOR.'logos'.DIRECTORY_SEPARATOR.$file);
			}
			$logo = EscposImage::load($folder_path.DIRECTORY_SEPARATOR.'logos'.DIRECTORY_SEPARATOR.$file, false);
			$this->printer->bitImage($logo);
		}
		
		if (isset($data->heading) && !empty($data->heading)) {
			$this->printer->setEmphasis(true);
			$this->printer->setTextSize(2, 2);
			$this->printer->text($data->heading . "\n");
			$this->printer->setEmphasis(false);
			$this->printer->setTextSize(1, 1);
			$this->printer->feed();
		}
		
		if (isset($data->header) && !empty($data->header)) {
			if (is_array($data->header)) {
				foreach ($data->header as $header) {
					$this->printer->text($header. "\n");
				}
			} else {
				$this->printer->text($data->header. "\n");
			}
			$this->printer->feed();
		}
		
		$this->printer->setJustification(Printer::JUSTIFY_LEFT);
		
		if (isset($data->info) && !empty($data->info)) {
			foreach ($data->info as $info) {
				$this->printer->text($info->label.': '.$info->value. "\n");
			}
			$this->printer->feed();
		}
		
		if (isset($data->items) && !empty($data->items)) {
			$r = 1;
			foreach ($data->items as $item) {
				$this->printer->text('#'.$r.' '.$this->product_name(addslashes($item->product_name)). "\n");
				$this->printer->text($this->printLine('   '.$item->quantity . " x " . $item->unit_price . ":  " . $item->subtotal). "\n");
				$r++;
			}
			$this->printer->feed();
		}
		
		if (isset($data->totals) && !empty($data->totals)) {
			foreach ($data->totals as $total) {
				if ($total->label == 'line') {
					$this->printer->text($this->drawLine());
				} else {
					$this->printer->text($this->printLine($total->label.': '.$total->value). "\n");
				}
			}
			$this->printer->feed();
		}

		if (isset($data->pre_footer) && !empty($data->pre_footer)) {
			foreach ($data->pre_footer as $pre_footer) {
				$this->printer->text($pre_footer->label.': '.$pre_footer->value. "\n");
			}
			$this->printer->feed();
		}
		
		if (isset($data->footer) && !empty($data->footer)) {
			$this->printer->setJustification(Printer::JUSTIFY_CENTER);
			$this->printer->feed(2);
			if (is_array($data->footer)) {
				foreach ($data->footer as $footer) {
					$this->printer->text($footer. "\n");
				}
			} else {
				$this->printer->text($data->footer . "\n");
			}
			$this->printer->feed();
		}
		
		$this->printer->feed();
		$this->printer->cut();
		$this->printer->close();
	}
	
	public function printData($data) {

		if (isset($data->logo) && !empty($data->logo)) {
			$file = basename($data->logo);
			$file_path = realpath(dirname(__FILE__));
			$folder_path = dirname($file_path);
			$this->printer->setJustification(Printer::JUSTIFY_CENTER);
			if (!file_exists($folder_path.DIRECTORY_SEPARATOR.'logos'.DIRECTORY_SEPARATOR.$file)) {
				copy($data->logo, $folder_path.DIRECTORY_SEPARATOR.'logos'.DIRECTORY_SEPARATOR.$file);
			}
			$logo = EscposImage::load($folder_path.DIRECTORY_SEPARATOR.'logos'.DIRECTORY_SEPARATOR.$file, false);
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
	
	function printLine($str, $size = NULL, $sep = ":", $space = NULL) {
		if (!$size) {
			$size = $this->char_per_line;
		}
		$size = $space ? $space : $size;
		$length = strlen($str);
		list($first, $second) = explode(":", $str, 2);
		$line = $first . ($sep == ":" ? $sep : '');
		for ($i = 1; $i < ($size - $length); $i++) {
			$line .= ' ';
		}
		$line .= ($sep != ":" ? $sep : '') . $second;
		return $line;
	}

	
}
