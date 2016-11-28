# VEI API
The VEI-API is a Virtual Entenerpise International PHP based Web Scraper with the purpose to
  - Retrive the users real name with given credentials
  - Get storemanager data for purchases (via Excel XLSX).

## Requirements
VEI-API  uses a few open source projects to work properly:
* [Goutte] - A Simple Web Scrapper for PHP
* [Spout] - Reading and Writing of CSV, XLSX and ODS files in PHP
* [Guzzle] - It's used by Goutte for html processing. I needed an up to date version.

VEI-API requires [PHP](http://php.net) v5.6+ to execute.  

There is a folder called **lib** which contains the composer files I downloaded and used.  
>Also in **lib\vendor\guzzlehttp\guzzle\src\Handler\CurlFactory.php** on like 78, **curl_reset($resource)** was removed because of a PHP version error. This may or may not needed to be removed based on your PHP version. 

## Installation

Download and extract the [master.zip](https://github.com/Import-Python/vei-api/archive/master.zip) and exract it.  
Install the folder into a directory on your server you wish to use.

Composer is not supported but feel free to use composer on all listed in requirements.

---
# Usage
To get the users real name with their credentials you would want to use:
```php
require_once __DIR__ . '/veiAPI.php'; //Require the lib, the path may differ
vei = new VEI(); //Initilize the VEI API
$vei->setCredentials('USERNAME', 'PASSWORD'); //Set users credentials.
$name = $vei->getRealname(); //Get the user realname and store it.
```
Also below is the purchase data from the XLSX file from the **storemanager** thats for download:
* The first parameter, **URL_ID** its usually found within the storemanager:  
  **portal.veinternational.org/storemanager/[URL_ID]/.../**
* The other two are dates, **START_DATE** & **END_DATE**, which need to have the format of date like: *MM/DD/YEAR* or for example **03/09/2016** to **11/13/2016** 


```php
require_once __DIR__ . '/veiAPI.php'; //Require the lib, the path differ
vei = new VEI(); //Initilize the VEI API
$vei->setCredentials('USERNAME', 'PASSWORD'); //Set users credentials.
$data = $vei->getExcelDataCustom('URL_ID', 'START_DATE', 'END_DATE'); //DATA
//$data = $vei->getExcelDataYesterday('URL_ID'); //GETS YESTERDAYS PURCHASES
/* Data is now an array which contains all the data listed below*/
```
When using the ExcelData functions, you get this data (in JSON only for readablity)
```JSON
{
  "<TRANSACTION NUMBER>": {
    "dateTime": {
      "date": "0000-00-00 00:00:00.000000",
      "timezone_type": 0,
      "timezone": ""
    },
    "billing_name": "",
    "billing_company": "",
    "billing_address": "",
    "billing_city": "",
    "billing_state": "",
    "billing_zip": "",
    "billing_country": "",
    "shipping_name": ")",
    "shipping_company": "",
    "shipping_address": "",
    "shipping_city": "",
    "shipping_state": "",
    "shipping_zip": "",
    "shipping_country": "",
    "email": "",
    "subtotal": 0,
    "shipping": 0,
    "tax": 0,
    "total": 0,
    "item_name": "",
    "item_number": "",
    "price": 0,
    "quantity": 0,
    "amount": 0
  }
}
```
---
# Practical Uses

I personally used it for getting the user **realname** and then compare the data to role out a automatic reminding system to make sure the pay monthly fees. (I sold Apartments for my firm)

# Upcoming Features:
* Allow for **promotional codes** to be created and removed (storemanager)
* Download all direct deposited money?
* Attendence tracking?
* Overall statics of the portal?

# About
Use at your own risk. If attemping to authenticate users, don't steal passwords and money from the virtual bank.


[//]: # (These are reference links used in the body of this note and get stripped out when the markdown processor does its job. There is no need to format nicely because it shouldn't be seen. Thanks SO - http://stackoverflow.com/questions/4823468/store-comments-in-markdown-syntax)

   [Goutte]: <https://github.com/FriendsOfPhp/Goutte>
   [Spout]: <https://github.com/box/spout>
   [Guzzle]: <https://github.com/guzzle/guzzle>

 
