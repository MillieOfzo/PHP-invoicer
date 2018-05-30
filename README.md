[![Travis Build Status](https://img.shields.io/travis/millieofzo/php-invoicer.svg)](https://travis-ci.org/millieofzo/php-invoicer)
[![Latest Stable Version](https://poser.pugx.org/millieofzo/php-invoicer/v/stable)](https://packagist.org/packages/millieofzo/php-invoicer)
[![Total Downloads](https://poser.pugx.org/millieofzo/php-invoicer/downloads)](https://packagist.org/packages/millieofzo/php-invoicer)
[![Latest Unstable Version](https://poser.pugx.org/millieofzo/php-invoicer/v/unstable)](https://packagist.org/packages/millieofzo/php-invoicer)
[![License](https://poser.pugx.org/millieofzo/php-invoicer/license)](https://packagist.org/packages/millieofzo/php-invoicer)

# PHP Invoicer

Features:
- PHP 7.2 Support
- PSR-4 compatible
- Available as composer package
- Dependencies are coming via composer

## Introduction

PHP Invoicer is a simple object oriented PHP class to generate beautifully designed invoices, quotes
or orders with just a few lines of code. Brand it with your own logo and theme color, add unlimited
items and total rows with automatic paging. You can deliver the PDF ouput in the user's browser,
save on the server or force a file download. PHP Invoicer is fully customizable and can be integrated
into any well known CMS.

### Multi-languages & Currencies

PHP Invoicer has built in translations in:
- English
- Dutch
- French
- German
- Spanish
- Italian 

You can easily add your own if needed and you can set the currency needed per document.

### Additional Titles, Paragraphs And Badges

Extra content (titles and multi-line paragraphs) can be added to the bottom of the document. You
might use it for payment or shipping information or any other content needed.

```php
/* Set badge */
$invoice->addBadge("Payment Paid");

/* Add title */
$invoice->addTitle("Important Notice");

/* Add Paragraph */
$invoice->addParagraph("No item will be replaced or refunded if you don't have the invoice with you. You can refund within 2 days of purchase.");

/* Set footer note */
$invoice->setFooternote("My Company Name Here");
```

## Installation

```bash
composer require millieofzo/php-invoicer
```

## Examples

There are 3 examples included in the `examples/` folder of this repo:
- simple.php
- example1.php
- invoice.php


### Create A New Invoice

In this simple example we are generating an invoice with custom logo and theme color. It will
contain 2 products and a box on the bottom with VAT 21% and total price. Then we add a "Paid" badge
right before the output.

```php
use MillieOfzo\PHPInvoicer\PHPInvoicer;

    $invoice = new PHPInvoicer('','€','en');
    
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
    
      /* Add totals */
    $invoice->addSubTotal();
    $invoice->addVatTotal(21);
    $invoice->addTotal(true);
    
    /* Set badge */
    $invoice->addBadge("Payment Paid");
    
    /* Render */
    /* I => Display on browser, D => Force Download, F => local path save, S => return document path */
    $invoice->render('example2.pdf','I');
```

## Documentation

### Create Instances

```php
use MillieOfzo\PHPInvoicer\PHPInvoicer;

// Default Param: Size: A4, Currency: $, Language: en
$invoice = new PHPInvoicer($size, $currency, $language); 
```

| Parameter | Type   | Accepts                          | Note                                                 |
|:----------|:-------|:---------------------------------|:-----------------------------------------------------|
| size      | string | A4 (default)<br>Letter<br>Legal  | Set your document size                               |
| currency  | string | any string (e.g. "$", "£", "€")  | Set the currency that you want to use                |
| language  | string | en (default), nl, fr, de, es, it | A language that exists in the `inc/languages` folder |


### Number Formatting

How do you want to show your numbers?

```php
$invoice->setNumberFormat($decimalpoint, $seperator);
```

| Parameter    | Type   | Accepts                     | Note                                       |
|:-------------|:-------|:----------------------------|:-------------------------------------------|
| decimalpoint | string | Commonly used is '.' or ',' | What string to use for decimal point       |
| seperator    | string | Commonly used is '.' or ',' | What string to use for thousands separator |

### Color

Set a custom color to personalize your invoices.

```php
// Hexadecimal color code
$invoice->setColor($color);
```

### Add Logo

```php
$invoice->setLogo($image, $maxwidth, $maxheight);
```

| Parameter            | Type   | Accepts                               | Note                                                                     |
|:---------------------|:-------|:--------------------------------------|:-------------------------------------------------------------------------|
| image                | string | Local path or remote url of the image | Preferably a good quality transparant png image                          |
| maxwidth (optional)  | int    |                                       | The width (in mm) of the bounding box where the image will be fitted in  |
| maxheight (optional) | int    |                                       | The height (in mm) of the bounding box where the image will be fitted in |

## Document Title

```php
// A string with the document title, will be displayed
// in the right top corner of the document (e.g. 'Invoice' or 'Quote')
$invoice->setType($title);
```

### Document order ID

```php
// Set order ID of document that will be displayed in
// the right top corner of the document (e.g. '2018052100012')
$invoice->setOrderid($orderid);
```

### Invoice Number

```php
// Document reference number that will be displayed in
// the right top corner of the document (e.g. 'INV29782')
$invoice->setReference($number);
```

### Date

```php
//A string with the document's date
$invoice->setDate($date);
```

### Due Date

```php
// A string with the document's due date
$invoice->setDue($duedate);
// Example
$invoice->setDue(date('d-m-Y',strtotime('+3 months')));
```

### Issuer Information

Set your company details.
An array with your company details. The first value of the array will be bold on the document so it's suggested to use your company's name. 
You can add as many lines as you need.
```php

/** Example: */
$invoice->setFrom([
    'My Company',
    'Address line 1',
    'Address line 2',
    'City and zip',
    'Country',
    'VAT number',
    'test'
]);
```

### Client Information

An array with your clients' details. 
The first value of the array will be bold on the document so we suggest you to use the company's name. You can add as many lines as you need.

Note: Keep the array count of Issuer and Client the same. Use empty value if necessary

```php
/** Example */
$invoice->setTo([
   'My Client',
   'Address line 1',
   'Address line 2',
   'City and zip',
   'Country',
   'VAT number',
   '' //Note keep count the same as issuer
]);
```


### Flip Flop

Switch the horizontal positions of your company information and the client information. By default,
your company details are on the left.

```php
$invoice->flipflop();
```

### Adding Items

Add a new product or service row to your document below the company and client information. PHP
Invoice has automatic paging so there is absolutely no limit.

```php
// $vat and $discount are optional
$invoice->addItem($name,$description,$quantity,$price,$vat = false,$discount = false);
```

| Parameter            | Type   | Accepts                               | Note                                                                     |
|:---------------------|:-------|:--------------------------------------|:-------------------------------------------------------------------------|
| name | string |  |  A string with the product or service name.  |
| description | string    |  | A string with the description with multi-line support. Use either <br> or \n to add a line-break. |
| quantity  | int |  | Specify the amount of the product |
| price  | int  | e.g 826 | The price of the product |
| vat  | int    |  e.g 21, 8 | Optional. Specify a vat percentage, which will calculate a 21% value from subtotal |
| discount  | int    |  e.g 10, 15, 20 | Optional. Specify a discount percentage, which will calculate a 10% discount on the subtotal|

### Adding Subtotal

Add a row below the products showing the calculated combined price amount of all products

```php
$invoice->addSubTotal();
```

### Adding Discount

Add a row below the products showing the calculated discount price. Specify the discount amount as a integer e.g 10.

```php
$invoice->addDiscountTotal($percent);
```

### Adding VAT

Add a row below the products showing the calculated VAT amount. VAT is calculated after discount. Specify the VAT amount as a integer e.g 21.

```php
$invoice->addVatTotal($percent);
```

### Adding Total

Add a row below the products and services with the total amount. Includes VAT amount and any discount amout

```php
$invoice->addTotal($colored);
```

### Adding Row

Add a row below the products and services for calculations and totals. You can add unlimited rows.

```php
$invoice->addRow($name,$value,$colored);
```

- $name {string} A string for the display name of the total
- $value {decimal} A decimal for the value. 
- $colored {boolean} Optional Set to true to set the theme color as background color of the row.

### Adding A Badge

Adds a badge to your invoice below the products and services. You can use this for example to
display that the invoice has been payed.

```php
$invoice->addBadge($badge, $color);
```

- $badge {string} A string with the text of the badge.
- $color {string} Optional. A string with the hex code of the color.

### Add Title

You can add titles and paragraphs to display information on the bottom part of your document such as
payment details or shipping information.

```php
$invoice->addTitle($title);
```

- $title {string} A string with the title to display in the badge.

### Add Paragraph

You can add titles and paragraphs to display information on the bottom part of your document such as
payment details or shipping information.

```php
$invoice->addParagraph($paragraph);
```

- $Paragraph {string} A string with the paragraph text with multi-line support. Use either <br> or \n to add a line-break.

### Footer

A small text you want to display on the bottom left corner of the document.

```php
$invoice->setFooternote($note);
```

- $note {string} A string with the information you want to display in the footer.

### Rendering The Invoice

```php
$invoice->render($name, $output);
// Example: 
// $invoice->render('invoice.pdf', 'S')
```

- $name {string} A string with the name of your invoice.
- $output {string} Choose how you want the invoice to be delivered to the user. 

The following options are available: 
- I (Send the file inline to the browser) 
- D (Send to the browser and force a file download with the name given by name) 
- F (Save to a local file. Make sure to set pass the path in the name parameter) 
- S (Return the document as a string)

## Credits

- [Splashpk](https://github.com/farjadtahir/pdf-invoicr)
- [FPDF](http://www.fpdf.org/)
