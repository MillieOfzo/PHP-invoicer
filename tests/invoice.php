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
	//$invoice->hide_tofrom();
	
	/* Adding Items in table */
	//$invoice->addItem(name,description,amount,vat,price,discount,total);
	$invoice->addItem("AMD Athlon X2DC-7450","2.4GHz/1GB/160GB/SMP-DVD/VB",6,false,580,false,3480);
	$invoice->addItem("PDC-E5300","2.6GHz/1GB/320GB/SMP-DVD/FDD/VB",4,false,645,false,2580);
	$invoice->addItem('LG 18.5" WLCD',"",10,false,230,false,2300);
	$invoice->addItem("HP LaserJet 5200","",1,false,1100,false,1100);
	
	/* Add totals */
	$price = 9460;
	$discount = $price * 0.10;
	$vat = ($price - $discount) * 0.21;
	$total = ($price - $discount) + $vat;
	
	$invoice->addTotal("Subtotal",$price);
	$invoice->addTotal("Discount 10%",$discount);
	$invoice->addTotal("VAT 21%",$vat);
	$invoice->addTotal("Total",$total,true);
	
	//$invoice->addBadge('Payed');
	
	$invoice->addParagraph('fsdfsd sdf s dfds sfds fds fsfd');
	$invoice->setFooternote('Copyright 2018 ASB security');
	/* Render */
	$invoice->render('example2.pdf','I'); 
	/* I => Display on browser, D => Force Download, F => local path save, S => return document path */ 