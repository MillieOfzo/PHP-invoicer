<?php
/**
 * Contains the PHPInvoicer class.
 *
 * @author      Farjad Tahir
 * @author      Roelof Jan van Golen
 * @license     MIT
 * @since       2017-12-15
 * @updated     2018-05-29
 *
 */

namespace MillieOfzo\PHPInvoicer;

use FPDF;

class PHPInvoicer extends FPDF
{
    public $angle = 0;

    public $font            = 'helvetica';        /* Font Name : See inc/fpdf/font for all supported fonts */
    public $columnOpacity   = 0.06;            /* Items table background color opacity. Range (0.00 - 1) */
    public $columnSpacing   = 0.3;                /* Spacing between Item Tables */
    public $referenceformat = ['.', ','];    /* Currency formater */
    public $margins         = [
        'l' => 15,
        't' => 15,
        'r' => 15
    ]; /* l: Left Side , t: Top Side , r: Right Side */

    public $lang;
    public $document;
    public $type;
    public $reference;
    public $logo;
    public $color;
    public $date;
    public $time;
    public $due;
    public $from;
    public $to;
    public $items;
    public $totals;
    public $subtotal;
    public $badge;
    public $badgeColor;
    public $addText;
    public $footernote;
    public $dimensions;
    public $display_tofrom = true;

    /**
     * GenerateInvoice constructor.
     * @param string $size
     * @param string $currency
     * @param string $language
     */
    public function __construct($size = 'A4', $currency = '$', $language = 'en')
    {
        $this->columns            = 4;
        $this->items              = [];
        $this->totals             = [];
        $this->subtotal           = 0;
        $this->addText            = [];
        $this->firstColumnWidth   = 70;
        $this->currency           = $currency;
        $this->maxImageDimensions = [230, 130];
        $this->setLanguage($language);
        $this->setDocumentSize($size);
        $this->setColor("#222222");

        parent::__construct('P', 'mm', [$this->document['w'], $this->document['h']]);

        $this->AliasNbPages();
        $this->SetMargins($this->margins['l'], $this->margins['t'], $this->margins['r']);
    }

    /**
     * @param $language
     */
    private function setLanguage($language)
    {
        $this->language = $language;
        include(dirname(__DIR__) . '/inc/languages/' . $language . '.inc');
        $this->lang = $lang;
    }

    /**
     * @param $dsize
     */
    private function setDocumentSize($dsize)
    {
        switch ($dsize) {
            case 'A4':
                $document['w'] = 210;
                $document['h'] = 297;
                break;
            case 'letter':
                $document['w'] = 215.9;
                $document['h'] = 279.4;
                break;
            case 'legal':
                $document['w'] = 215.9;
                $document['h'] = 355.6;
                break;
            default:
                $document['w'] = 210;
                $document['h'] = 297;
                break;
        }
        $this->document = $document;
    }

    /**
     * @param $image
     * @return array
     */
    private function resizeToFit($image)
    {
        list($width, $height) = getimagesize($image);
        $newWidth  = $this->maxImageDimensions[0] / $width;
        $newHeight = $this->maxImageDimensions[1] / $height;
        $scale     = min($newWidth, $newHeight);

        return [
            round($this->pixelsToMM($scale * $width)),
            round($this->pixelsToMM($scale * $height))
        ];
    }

    /**
     * @param $val
     * @return float|int
     */
    private function pixelsToMM($val)
    {
        $mm_inch = 25.4;
        $dpi     = 96;

        return ($val * $mm_inch) / $dpi;
    }

    /**
     * @param $hex
     * @return array
     */
    private function hex2rgb($hex)
    {
        $hex = str_replace("#", "", $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $rgb = [$r, $g, $b];

        return $rgb;
    }

    /**
     * @param $string
     * @return null|string|string[]
     */
    private function br2nl($string)
    {
        return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
    }

    /**
     * @param $zone
     * @return bool
     */
    public function isValidTimezoneId($zone)
    {
        try {
            new DateTimeZone($zone);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param string $zone
     */
    public function setTimeZone($zone = "")
    {
        if (!empty($zone) and $this->isValidTimezoneId($zone) === true) {
            date_default_timezone_set($zone);
        }
    }

    /**
     * @param $title
     */
    public function setType($title)
    {
        $this->title = $title;
    }

    /**
     * @param $rgbcolor
     */
    public function setColor($rgbcolor)
    {
        $this->color = $this->hex2rgb($rgbcolor);
    }

    /**
     * @param $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @param $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * @param $date
     */
    public function setDue($date)
    {
        $this->due = $date;
    }

    /**
     * @param int $logo
     * @param int $maxWidth
     * @param int $maxHeight
     */
    public function setLogo($logo = 0, $maxWidth = 0, $maxHeight = 0)
    {
        if ($maxWidth and $maxHeight) {
            $this->maxImageDimensions = [$maxWidth, $maxHeight];
        }
        $this->logo       = $logo;
        $this->dimensions = $this->resizeToFit($logo);
    }

    /**
     * Hide your company information and the client information
     */
    public function hide_tofrom()
    {
        $this->display_tofrom = false;
    }

    /**
     * @param $data
     */
    public function setFrom($data)
    {
        $this->from = (array)$data;
    }

    /**
     * @param $data
     */
    public function setTo($data)
    {
        $this->to = $data;
    }

    /**
     * @param $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @param $orderid
     */
    public function setOrderid($orderid)
    {
        $this->orderid = $orderid;
    }

    /**
     * @param $decimals
     * @param $thousands_sep
     */
    public function setNumberFormat($decimals, $thousands_sep)
    {
        $this->referenceformat = [$decimals, $thousands_sep];
    }

    /**
     * Switch the horizontal positions of your company information and the client information. By default, your company details are on the left.
     */
    public function flipflop()
    {
        $this->flipflop = true;
    }

    /**
     * Add the invoice items
     *
     * @param string $item Item name
     * @param string $description Item description
     * @param int    $quantity Quantity specified as integer
     * @param int    $price Product price specified as integer. Will be converted to decimal
     * @param bool   $vat Specify vat as a percent integer value. e.g 21, 8 etc.
     * @param bool   $discount Specify discount as a percent integer value. e.g 21, 8 etc.
     */
    public function addItem($item = "", $description = "", $quantity = 0, $price = 0, $vat = false, $discount = false)
    {
        $p['item']        = $item;
        $p['description'] = $this->br2nl($description);
        $p['quantity'] = $quantity;
        $p['price']    = $price;
		$p['total']    = $price * $quantity;
		
		// discount is in percent
        if ($vat !== false) {
            $p['vat'] = ($p['price'] / 100) * $vat;
            $p['num'] = ($p['price'] / 100) * $vat;
            if (is_numeric($p['vat'])) {
                $p['vat'] = $this->currency . ' ' . number_format($p['vat'], 2, $this->referenceformat[0],
                        $this->referenceformat[1]);
            }
            $this->vatField = true;
            $this->columns  = 5;
			$p['total'] = $p['total'] + $p['num'];
        }		
        
		// discount is in percent
        if ($discount !== false) {
            $this->firstColumnWidth = 58;
            $p['discount']          = ($p['price'] / 100) * $discount * $quantity;
            $p['num']          = ($p['price'] / 100) * $discount * $quantity;
            if (is_numeric($p['discount'])) {
                $p['discount'] = $this->currency . ' ' . number_format($p['discount'], 2, $this->referenceformat[0],
                        $this->referenceformat[1]);
            }
			$p['total'] = $p['total'] - $p['num'];
            $this->discountField = true;
			if(($vat !== false))
			{
				$this->columns       = 6;
			}
			else
			{
				$this->columns       = 5;
			}
            
        }
        $this->items[] = $p;
		
		$this->subtotal += $p['total'];
		//var_dump( $this->items);
    }

    /**
     * Add subtotal column. Get value from the combined prices of all specified items
     *
     * @param int  $value
     * @param bool $colored
     */
    public function addSubTotal($value = 0, $colored = false)
    {
		if($this->subtotal > 0)
		{
			$value = $this->subtotal;
		}
        $t['name']  = $this->lang['subtotal'];
        $t['value'] = $value;
        $t['num'] = $value;
        if (is_numeric($t['value'])) {
            $t['value'] = $this->currency . ' ' . number_format($t['value'], 2, $this->referenceformat[0],
                    $this->referenceformat[1]);
        }
        $t['colored']   = $colored;
        $this->totals['subtotal'] = $t;
    }

    /**
     * Show discount column
     *
     * @param      $percent
     * @param bool $colored
     */
    public function addDiscountTotal($percent, $colored = false)
    {
        $t['name']  = $this->lang['discount'] . ' ' . $percent . '%';
        $t['value'] = ($this->subtotal / 100) * $percent;
        $t['num'] = ($this->subtotal / 100) * $percent;
        if (is_numeric($t['value'])) {
            $t['value'] = '- '.$this->currency . ' ' . number_format($t['value'], 2, $this->referenceformat[0],
                    $this->referenceformat[1]);
        }
        $t['colored']   = $colored;
        $this->totals['discount'] = $t;
    }

    /**
     * Show vat column. If discount is specified, vat is calculated after discount is applied
     *
     * @param      $percent
     * @param bool $colored
     */
    public function addVatTotal($percent, $colored = false)
    {
        $t['name']  = $this->lang['vat'] . ' ' . $percent . '%';
		if(isset($this->totals['discount']))
		{
			$t['value'] = (($this->totals['subtotal']['num'] - $this->totals['discount']['num']) / 100) * $percent;
			$t['num'] = (($this->totals['subtotal']['num'] - $this->totals['discount']['num']) / 100) * $percent;			
		}
		else
		{
			$t['value'] = ($this->totals['subtotal']['num'] / 100) * $percent;
			$t['num'] = ($this->totals['subtotal']['num'] / 100) * $percent;			
		}
        if (is_numeric($t['value'])) {
            $t['value'] = $this->currency . ' ' . number_format($t['value'], 2, $this->referenceformat[0],
                    $this->referenceformat[1]);
        }
        $t['colored']   = $colored;
        $this->totals['vat'] = $t;
		//var_dump($this->totals);
    }

    /**
     * Show total amount column.
     *
     * @param bool $colored
     */
    public function addTotal($colored = false)
    {
        $t['name']  = $this->lang['total'];
        $t['value'] = $this->totals['subtotal']['num'];

        if(isset($this->totals['vat']))
        {
            $t['value'] = $this->totals['subtotal']['num'] + $this->totals['vat']['num'];
        }
		if(isset($this->totals['discount']))
		{
			$t['value'] = $t['value'] - $this->totals['discount']['num'];
		}
        if (is_numeric($t['value'])) {
            $t['value'] = $this->currency . ' ' . number_format($t['value'], 2, $this->referenceformat[0],
                    $this->referenceformat[1]);
        }
        $t['colored']   = $colored;
        $this->totals[] = $t;
    }

    /**
     * Show a additional row.
     *
     * @param string $name
     * @param int $value
     * @param bool $colored
     */
    public function addRow($name,$value,$colored = false)
    {
        $t['name']  = $name;
        $t['value'] = $value;

        if (is_numeric($t['value'])) {
            $t['value'] = $this->currency . ' ' . number_format($t['value'], 2, $this->referenceformat[0],
                    $this->referenceformat[1]);
        }
        $t['colored']   = $colored;
        $this->totals[] = $t;
    }	
	
    /**
     * @param $title
     */
    public function addTitle($title)
    {
        $this->addText[] = ['title', $title];
    }

    /**
     * @param $paragraph
     */
    public function addParagraph($paragraph)
    {
        $paragraph       = $this->br2nl($paragraph);
        $this->addText[] = ['paragraph', $paragraph];
    }

    /**
     * @param $badge
     */
    public function addBadge($badge, $color = false)
    {
        $this->badge = $badge;
		$this->badgeColor = $this->color;
		if($color != false)
		{
			$this->badgeColor = $this->hex2rgb($color);
		}
        
    }

    /**
     * @param $note
     */
    public function setFooternote($note)
    {
        $this->footernote = $note;
    }

    /**
     * @param string $name
     * @param string $destination
     */
    public function render($name = '', $destination = '')
    {
        $this->AddPage();
        $this->Body();
        $this->AliasNbPages();
        $this->Output($name, $destination);
    }

    /**
     * Generate the table headers
     */
    public function Header()
    {
        if (isset($this->logo) and !empty($this->logo)) {
            $this->Image($this->logo, $this->margins['l'], $this->margins['t'], $this->dimensions[0],
                $this->dimensions[1]);
        }

        //Title
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font, 'B', 20);
        if(isset($this->title) and !empty($this->title)) {
            $this->Cell(0, 5, iconv("UTF-8", "ISO-8859-1", strtoupper($this->title)), 0, 1, 'R');
        }
        $this->SetFont($this->font, '', 9);
        $this->Ln(5);

        $lineheight = 5;
        //Calculate position of strings
        $this->SetFont($this->font, 'B', 9);
        $positionX = $this->document['w'] - $this->margins['l'] - $this->margins['r'] - max(strtoupper($this->GetStringWidth($this->lang['number'])),
                strtoupper($this->GetStringWidth($this->lang['date'])),
                strtoupper($this->GetStringWidth($this->lang['due']))) - 35;

		// Orderid
        if (!empty($this->orderid)) {
            $this->Cell($positionX, $lineheight);
            $this->SetTextColor($this->color[0], $this->color[1], $this->color[2]);
            $this->Cell(32, $lineheight, iconv("UTF-8", "ISO-8859-1", strtoupper($this->lang['orderid']) . ':'), 0, 0,'L');
            $this->SetTextColor(50, 50, 50);
            $this->SetFont($this->font, '', 9);
            $this->Cell(0, $lineheight, $this->orderid, 0, 1, 'R');
        }				
				
        // Number
        if (!empty($this->reference)) {
            $this->Cell($positionX, $lineheight);
            $this->SetTextColor($this->color[0], $this->color[1], $this->color[2]);
            $this->Cell(32, $lineheight, iconv("UTF-8", "ISO-8859-1", strtoupper($this->lang['number']) . ':'), 0, 0,'L');
            $this->SetTextColor(50, 50, 50);
            $this->SetFont($this->font, '', 9);
            $this->Cell(0, $lineheight, $this->reference, 0, 1, 'R');
        }
				
        //Date
        $this->Cell($positionX, $lineheight);
        $this->SetFont($this->font, 'B', 9);
        $this->SetTextColor($this->color[0], $this->color[1], $this->color[2]);
        $this->Cell(32, $lineheight, iconv("UTF-8", "ISO-8859-1", strtoupper($this->lang['date'])) . ':', 0, 0, 'L');
        $this->SetTextColor(50, 50, 50);
        $this->SetFont($this->font, '', 9);
        $this->Cell(0, $lineheight, $this->date, 0, 1, 'R');

        //Time
        if (!empty($this->time)) {
            $this->Cell($positionX, $lineheight);
            $this->SetFont($this->font, 'B', 9);
            $this->SetTextColor($this->color[0], $this->color[1], $this->color[2]);
            $this->Cell(32, $lineheight, iconv("UTF-8", "ISO-8859-1", strtoupper($this->lang['time'])) . ':', 0, 0,
                'L');
            $this->SetTextColor(50, 50, 50);
            $this->SetFont($this->font, '', 9);
            $this->Cell(0, $lineheight, $this->time, 0, 1, 'R');
        }
        //Due date
        if (!empty($this->due)) {
            $this->Cell($positionX, $lineheight);
            $this->SetFont($this->font, 'B', 9);
            $this->SetTextColor($this->color[0], $this->color[1], $this->color[2]);
            $this->Cell(32, $lineheight, iconv("UTF-8", "ISO-8859-1", strtoupper($this->lang['due'])) . ':', 0, 0, 'L');
            $this->SetTextColor(50, 50, 50);
            $this->SetFont($this->font, '', 9);
            $this->Cell(0, $lineheight, $this->due, 0, 1, 'R');
        }

        //First page
        if ($this->PageNo() == 1) {
            if (($this->margins['t'] + $this->dimensions[1]) > $this->GetY()) {
                $this->SetY($this->margins['t'] + $this->dimensions[1] + 5);
            } else {
                $this->SetY($this->GetY() + 10);
            }
            $this->Ln(5);
            $this->SetFillColor($this->color[0], $this->color[1], $this->color[2]);
            $this->SetTextColor($this->color[0], $this->color[1], $this->color[2]);

            $this->SetDrawColor($this->color[0], $this->color[1], $this->color[2]);
            $this->SetFont($this->font, 'B', 10);
            $width = ($this->document['w'] - $this->margins['l'] - $this->margins['r']) / 2;
            if (isset($this->flipflop)) {
                $to                 = $this->lang['to'];
                $from               = $this->lang['from'];
                $this->lang['to']   = $from;
                $this->lang['from'] = $to;
                $to                 = $this->to;
                $from               = $this->from;
                $this->to           = $from;
                $this->from         = $to;
            }

            if ($this->display_tofrom === true) {
                $this->Cell($width, $lineheight, strtoupper($this->lang['from']), 0, 0, 'L');
                $this->Cell(0, $lineheight, strtoupper($this->lang['to']), 0, 0, 'L');
                $this->Ln(7);
                $this->SetLineWidth(0.4);
                $this->Line($this->margins['l'], $this->GetY(), $this->margins['l'] + $width - 10, $this->GetY());
                $this->Line($this->margins['l'] + $width, $this->GetY(), $this->margins['l'] + $width + $width,
                    $this->GetY());

                //Information
                $this->Ln(5);
                $this->SetTextColor(50, 50, 50);
                $this->SetFont($this->font, 'B', 10);
                $this->Cell($width, $lineheight, $this->from[0], 0, 0, 'L');
                $this->Cell(0, $lineheight, $this->to[0], 0, 0, 'L');
                $this->SetFont($this->font, '', 8);
                $this->SetTextColor(100, 100, 100);
                $this->Ln(7);
                for ($i = 1; $i < max(count([$this->from],1), count([$this->to],1)) -1; $i++) {
                    $this->Cell($width, $lineheight, iconv("UTF-8", "ISO-8859-1", $this->from[$i]), 0, 0, 'L');
                    $this->Cell(0, $lineheight, iconv("UTF-8", "ISO-8859-1", $this->to[$i]), 0, 0, 'L');
                    $this->Ln(5);
                }

                $this->Ln(-6);
                $this->Ln(5);
            } else {
                $this->Ln(-10);
            }
        }
        //Table header
        if (!isset($this->productsEnded)) {
            $width_other = ($this->document['w'] - $this->margins['l'] - $this->margins['r'] - $this->firstColumnWidth - ($this->columns * $this->columnSpacing)) / ($this->columns - 1);
            $this->SetTextColor(50, 50, 50);
            $this->Ln(12);
            $this->SetFont($this->font, 'B', 9);
            $this->Cell(1, 10, '', 0, 0, 'L', 0);
            $this->Cell($this->firstColumnWidth, 10, iconv("UTF-8", "ISO-8859-1", strtoupper($this->lang['product'])),
                0, 0, 'L', 0);
            $this->Cell($this->columnSpacing, 10, '', 0, 0, 'L', 0);
            $this->Cell($width_other, 10, iconv("UTF-8", "ISO-8859-1", strtoupper($this->lang['qty'])), 0, 0, 'C', 0);

            $this->Cell($this->columnSpacing, 10, '', 0, 0, 'L', 0);
            $this->Cell($width_other, 10, iconv("UTF-8", "ISO-8859-1", strtoupper($this->lang['price'])), 0, 0, 'C', 0);
			if (isset($this->vatField)) {
                $this->Cell($this->columnSpacing, 10, '', 0, 0, 'L', 0);
                $this->Cell($width_other, 10, iconv("UTF-8", "ISO-8859-1", strtoupper($this->lang['vat'])), 0, 0, 'C',
                    0);
            }
            if (isset($this->discountField)) {
                $this->Cell($this->columnSpacing, 10, '', 0, 0, 'L', 0);
                $this->Cell($width_other, 10, iconv("UTF-8", "ISO-8859-1", strtoupper($this->lang['discount'])), 0, 0,
                    'C', 0);
            }
            $this->Cell($this->columnSpacing, 10, '', 0, 0, 'L', 0);
            $this->Cell($width_other, 10, iconv("UTF-8", "ISO-8859-1", strtoupper($this->lang['total'])), 0, 0, 'C', 0);
            $this->Ln();
            $this->SetLineWidth(0.3);
            $this->SetDrawColor($this->color[0], $this->color[1], $this->color[2]);
            $this->Line($this->margins['l'], $this->GetY(), $this->document['w'] - $this->margins['r'], $this->GetY());
            $this->Ln(2);
        } else {
            $this->Ln(12);
        }
    }

    /**
     * Generate the table body
     */
    public function Body()
    {
        $width_other = ($this->document['w'] - $this->margins['l'] - $this->margins['r'] - $this->firstColumnWidth - ($this->columns * $this->columnSpacing)) / ($this->columns - 1);
        $cellHeight  = 8;
        $bgcolor     = (1 - $this->columnOpacity) * 255;
        if ($this->items) {
            foreach ($this->items as $item) {
                if ($item['description']) {
                    //Precalculate height
                    $calculateHeight = new self;
                    $calculateHeight->addPage();
                    $calculateHeight->setXY(0, 0);
                    $calculateHeight->SetFont($this->font, '', 7);
                    $calculateHeight->MultiCell($this->firstColumnWidth, 3,
                        iconv("UTF-8", "ISO-8859-1", $item['description']), 0, 'L', 1);
                    $descriptionHeight = $calculateHeight->getY() + $cellHeight + 2;
                    $pageHeight        = $this->document['h'] - $this->GetY() - $this->margins['t'] - $this->margins['t'];
                    if ($pageHeight < 35) {
                        $this->AddPage();
                    }
                }
                $cHeight = $cellHeight;
                $this->SetFont($this->font, 'b', 8);
                $this->SetTextColor(50, 50, 50);
                $this->SetFillColor($bgcolor, $bgcolor, $bgcolor);
                $this->Cell(1, $cHeight, '', 0, 0, 'L', 1);
                $x = $this->GetX();
                $this->Cell($this->firstColumnWidth, $cHeight, iconv("UTF-8", "ISO-8859-1", $item['item']), 0, 0, 'L',
                    1);
                if ($item['description']) {
                    $resetX = $this->GetX();
                    $resetY = $this->GetY();
                    $this->SetTextColor(120, 120, 120);
                    $this->SetXY($x, $this->GetY() + 8);
                    $this->SetFont($this->font, '', 7);
                    $this->MultiCell($this->firstColumnWidth, 3, iconv("UTF-8", "ISO-8859-1", $item['description']), 0,
                        'L', 1);
                    //Calculate Height
                    $newY    = $this->GetY();
                    $cHeight = $newY - $resetY + 2;
                    //Make our spacer cell the same height
                    $this->SetXY($x - 1, $resetY);
                    $this->Cell(1, $cHeight, '', 0, 0, 'L', 1);
                    //Draw empty cell
                    $this->SetXY($x, $newY);
                    $this->Cell($this->firstColumnWidth, 2, '', 0, 0, 'L', 1);
                    $this->SetXY($resetX, $resetY);
                }
                $this->SetTextColor(50, 50, 50);
                $this->SetFont($this->font, '', 8);
                $this->Cell($this->columnSpacing, $cHeight, '', 0, 0, 'L', 0);
                $this->Cell($width_other, $cHeight, $item['quantity'], 0, 0, 'C', 1);
                $this->Cell($this->columnSpacing, $cHeight, '', 0, 0, 'L', 0);
                $this->Cell($this->columnSpacing, $cHeight, '', 0, 0, 'L', 0);
                $this->Cell($width_other, $cHeight, iconv('UTF-8', 'windows-1252',
                    $this->currency . ' ' . number_format($item['price'], 2, $this->referenceformat[0],
                        $this->referenceformat[1])), 0, 0, 'C', 1);
                if (isset($this->vatField)) {
                    $this->Cell($this->columnSpacing, $cHeight, '', 0, 0, 'L', 0);
                    if (isset($item['vat'])) {
                        $this->Cell($width_other, $cHeight, iconv('UTF-8', 'windows-1252', $item['vat']), 0, 0, 'C', 1);
                    } else {
                        $this->Cell($width_other, $cHeight, '', 0, 0, 'C', 1);
                    }

                }						
                if (isset($this->discountField)) {
                    $this->Cell($this->columnSpacing, $cHeight, '', 0, 0, 'L', 0);
                    if (isset($item['discount'])) {
                        $this->Cell($width_other, $cHeight, iconv('UTF-8', 'windows-1252', $item['discount']), 0, 0,
                            'C', 1);
                    } else {
                        $this->Cell($width_other, $cHeight, '', 0, 0, 'C', 1);
                    }
                }
                $this->Cell($this->columnSpacing, $cHeight, '', 0, 0, 'L', 0);
                $this->Cell($width_other, $cHeight, iconv('UTF-8', 'windows-1252',
                    $this->currency . ' ' . number_format($item['total'], 2, $this->referenceformat[0],
                        $this->referenceformat[1])), 0, 0, 'C', 1);
                $this->Ln();
                $this->Ln($this->columnSpacing);
            }
        }
        $badgeX = $this->getX();
        $badgeY = $this->getY();

        //Add totals
        if ($this->totals) {
            foreach ($this->totals as $total) {
                $this->SetTextColor(50, 50, 50);
                $this->SetFillColor($bgcolor, $bgcolor, $bgcolor);
                $this->Cell(1 + $this->firstColumnWidth, $cellHeight, '', 0, 0, 'L', 0);
                for ($i = 0; $i < $this->columns - 3; $i++) {
                    $this->Cell($width_other, $cellHeight, '', 0, 0, 'L', 0);
                    $this->Cell($this->columnSpacing, $cellHeight, '', 0, 0, 'L', 0);
                }
                $this->Cell($this->columnSpacing, $cellHeight, '', 0, 0, 'L', 0);
                if ($total['colored']) {
                    $this->SetTextColor(255, 255, 255);
                    $this->SetFillColor($this->color[0], $this->color[1], $this->color[2]);
                }
                $this->SetFont($this->font, 'b', 8);
                $this->Cell(1, $cellHeight, '', 0, 0, 'L', 1);
                $this->Cell($width_other - 1, $cellHeight, iconv('UTF-8', 'windows-1252', $total['name']), 0, 0, 'L',
                    1);
                $this->Cell($this->columnSpacing, $cellHeight, '', 0, 0, 'L', 0);
                $this->SetFont($this->font, 'b', 8);
                $this->SetFillColor($bgcolor, $bgcolor, $bgcolor);
                if ($total['colored']) {
                    $this->SetTextColor(255, 255, 255);
                    $this->SetFillColor($this->color[0], $this->color[1], $this->color[2]);
                }
                $this->Cell($width_other, $cellHeight, iconv('UTF-8', 'windows-1252', $total['value']), 0, 0, 'C', 1);
                $this->Ln();
                $this->Ln($this->columnSpacing);
            }
        }
        $this->productsEnded = true;
        $this->Ln();
        $this->Ln(3);


        //Badge
        if ($this->badge) {
            $badge  = ' ' . strtoupper($this->badge) . ' ';
            $resetX = $this->getX();
            $resetY = $this->getY();
            $this->setXY($badgeX, $badgeY + 15);
            $this->SetLineWidth(0.4);
			
			$color = $this->badgeColor;
			
            $this->SetDrawColor($color[0], $color[1], $color[2]);
            $this->setTextColor($color[0], $color[1], $color[2]);
            $this->SetFont($this->font, 'b', 15);
            $this->Rotate(10, $this->getX(), $this->getY());
            $this->Rect($this->GetX(), $this->GetY(), $this->GetStringWidth($badge) + 2, 10);
            $this->Write(10, $badge);
            $this->Rotate(0);
            if ($resetY > $this->getY() + 20) {
                $this->setXY($resetX, $resetY);
            } else {
                $this->Ln(18);
            }
        }

        //Add information
        foreach ($this->addText as $text) {
            if ($text[0] == 'title') {
                $this->SetFont($this->font, 'b', 9);
                $this->SetTextColor(50, 50, 50);
                $this->Cell(0, 10, iconv("UTF-8", "ISO-8859-1", strtoupper($text[1])), 0, 0, 'L', 0);
                $this->Ln();
                $this->SetLineWidth(0.3);
                $this->SetDrawColor($this->color[0], $this->color[1], $this->color[2]);
                $this->Line($this->margins['l'], $this->GetY(), $this->document['w'] - $this->margins['r'],
                    $this->GetY());
                $this->Ln(4);
            }
            if ($text[0] == 'paragraph') {
                $this->SetTextColor(80, 80, 80);
                $this->SetFont($this->font, '', 8);
                $this->MultiCell(0, 4, iconv("UTF-8", "ISO-8859-1", $text[1]), 0, 'L', 0);
                $this->Ln(4);
            }
        }
    }

    /**
     * Generate footer on page
     */
    public function Footer()
    {
        $this->SetY(-$this->margins['t']);
        $this->SetFont($this->font, '', 8);
        $this->SetTextColor(50, 50, 50);
        $this->Cell(0, 10, $this->footernote, 0, 0, 'L');
        $this->Cell(0, 10, $this->lang['page'] . ' ' . $this->PageNo() . ' ' . $this->lang['page_of'] . ' {nb}', 0, 0,
            'R');
    }

    /**
     * @param     $angle
     * @param int $x
     * @param int $y
     */
    public function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1) {
            $x = $this->x;
        }
        if ($y == -1) {
            $y = $this->y;
        }
        if ($this->angle != 0) {
            $this->_out('Q');
        }
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c     = cos($angle);
            $s     = sin($angle);
            $cx    = $x * $this->k;
            $cy    = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy,
                -$cx, -$cy));
        }
    }

    /**
     * End page
     */
    public function _endpage()
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

}
