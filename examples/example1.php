<?php
require '../vendor/autoload.php';

use MillieOfzo\PHPInvoicer\GenerateInvoice;

$invoice = new GenerateInvoice('','€','en');

/* Header Settings */
$invoice->setTimeZone('America/Los_Angeles');
$invoice->setLogo("../examples/images/ti_logo_yellow.png");
$invoice->setColor("#4d4c59");
$invoice->setType("Sale Invoice");
$invoice->setReference("INV-55033645");
$invoice->setDate(date('M dS ,Y',time()));
$invoice->setTime(date('h:i:s A',time()));
$invoice->setDue(date('M dS ,Y',strtotime('+3 months')));
$invoice->setFrom(array("Seller Name","Sample Company Name","128 AA Juanita Ave","Glendora , CA 91740","United States of America"));
$invoice->setTo(array("Purchaser Name","Sample Company Name","128 AA Juanita Ave","Glendora , CA 91740","United States of America"));

/* Adding Items in table */
$invoice->addItem("AMD Athlon X2DC-7450","2.4GHz/1GB/160GB/SMP-DVD/VB",2,2);
$invoice->addItem("PDC-E5300","2.6GHz/1GB/320GB/SMP-DVD/FDD/VB",4,645);
$invoice->addItem('LG 18.5" WLCD',"",10,230);
$invoice->addItem("HP LaserJet 5200","",1,1100);

/* Add totals */
$invoice->addSubTotal();
$invoice->addVatTotal(21);
$invoice->addTotal(true);

/* Set badge */
$invoice->addBadge("Payment Paid");

/* Add title */
$invoice->addTitle("Important Notice");

/* Add Paragraph */
$invoice->addParagraph("No item will be replaced or refunded if you don't have the invoice with you. You can refund within 2 days of purchase.");

/* Set footer note */
$invoice->setFooternote("My Company Name Here");

/* Render */
$invoice->render('example1.pdf','I'); /* I => Display on browser, D => Force Download, F => local path save, S => return document path */

