<?php
require '../src/phpinvoicer.php';

$invoice = new GenerateInvoice('','€','en');

/* Header Settings */
$invoice->setLogo("../examples/images/ti_logo_yellow.png");
$invoice->setColor("#4d4c59");
$invoice->setType("Simple Invoice");
$invoice->setOrderid("2018052100012");
$invoice->setReference("55033645");
$invoice->setDate(date('d-m-Y',time()));
$invoice->setDue(date('d-m-Y',strtotime('+3 months')));
$invoice->hide_tofrom();

/* Adding Items in table */
$invoice->addItem("AMD Athlon X2DC-7450","2.4GHz/1GB/160GB/SMP-DVD/VB",2,2);
$invoice->addItem("PDC-E5300","2.6GHz/1GB/320GB/SMP-DVD/FDD/VB",4,645);
$invoice->addItem('LG 18.5" WLCD',"",10,230);
$invoice->addItem("HP LaserJet 5200","",1,1100);

  /* Add totals */
$invoice->addSubTotal();
$invoice->addVatTotal(21);
$invoice->addTotal(true);

/* Render */
/* I => Display on browser, D => Force Download, F => local path save, S => return document path */
$invoice->render('example2.pdf','I');

