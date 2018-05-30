<?php
require '../vendor/autoload.php';

use MillieOfzo\PHPInvoicer\GenerateInvoice;

$invoice = new GenerateInvoice('','€','en');

/* Header Settings */
$invoice->setLogo("../examples/images/ti_logo_yellow.png");
$invoice->setColor("#4d4c59");
$invoice->setType("Invoice #75");
$invoice->setOrderid("2018052100012");
$invoice->setReference("55033645");
$invoice->setDate(date('d-m-Y',time()));
$invoice->setDue(date('d-m-Y',strtotime('+3 months')));

$invoice->setNumberFormat('.', '');

/* To and from */
$invoice->setFrom(array("Seller Name","Sample Company Name","128 AA Juanita Ave","Glendora , CA 91740","United States of America"));
$invoice->setTo(array("Purchaser Name","Sample Company Name","128 AA Juanita Ave","Glendora , CA 91740","United States of America"));

/* Adding Items in table */
$invoice->addItem("AMD Athlon X2DC-7450","2.4GHz/1GB/160GB/SMP-DVD/VB",2,2,21);
$invoice->addItem("PDC-E5300","2.6GHz/1GB/320GB/SMP-DVD/FDD/VB",4,645,21);
$invoice->addItem('LG 18.5" WLCD',"",10,230,21);
$invoice->addItem("HP LaserJet 5200","",1,1100,21);

/* Add totals */
$invoice->addSubTotal();
$invoice->addDiscountTotal(10);
$invoice->addRow('Swift send', 10);
$invoice->addTotal(true);

//$invoice->addBadge('Payed');

$invoice->addParagraph('fsdfsd sdf s dfds sfds fds fsfd');
$invoice->setFooternote('Copyright 2018 ASB security');
/* Render */
$invoice->render('example2.pdf','I');
/* I => Display on browser, D => Force Download, F => local path save, S => return document path */