<?php
/**
 * Contains the BaseClassTest class.
 *
 * @copyright   Copyright (c) 2017 Attila Fulop
 * @author      Attila Fulop
 * @license     GPL
 * @since       2017-12-15
 *
 */


namespace MillieOfzo\PHPInvoicer\Tests;

require '../vendor/autoload.php';
use MillieOfzo\PHPInvoicer\GenerateInvoice;
use PHPUnit\Framework\TestCase;

class BaseClassTest extends TestCase
{
    /**
     * @test
     */
    public function can_be_instantiated()
    {
        $invoicr =  new GenerateInvoice();

         $this->assertInstanceOf(GenerateInvoice::class, $invoicr);
    }

}
