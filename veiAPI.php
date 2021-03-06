<?php
 /*
 __      ________ _____             _____ _____
 \ \    / /  ____|_   _|      /\   |  __ \_   _|
  \ \  / /| |__    | |______ /  \  | |__) || |
   \ \/ / |  __|   | |______/ /\ \ |  ___/ | |
    \  /  | |____ _| |_    / ____ \| |    _| |_
     \/   |______|_____|  /_/    \_\_|   |_____|
        by Brendan Fuller (c) (2016)

*/

/*****************IMPORT ALL LIBRARIES************************/

require_once __DIR__ . '/lib/vendor/autoload.php';

use Goutte\Client;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;

/***********************************************************/



/*
   Welcome to the main class!
*/
class VEI {

  public $user; //Username
  private $pass; //Password
  private $status = false; //Status if user is signed in or not

  /*
    THIS SETS THE CREDENTAILS OF THE USER
    @param Username is the name used for signing in to the portal.
    @param Password is a password... Not much to explain here.
  */
  public function setCredentials($username, $password) {
    $this->user = $username;
    $this->pass = $password;
    $this->status = true;
  }
  /*
    THIS RETEIVES THE USERS INFO FOR USE IN OTHER FUNCTION
  */
  private function getFirmInfo() {
    /* Checks if credentials have been set*/
    if ($this->status == false) {
      return false; //Returns false for invaild credentials
    }

    $userNode = '#user_section > b'; //Its what is used to grab the name in HTML Scrapping

    $client = new Client(); //Create a new Goutte Client
    $crawler = $client->request('GET', 'https://portal.veinternational.org/login/'); //Request the website to visit
    $form = $crawler->selectButton('Sign in')->form(); //Find the form to login with
    $crawler = $client->submit($form, array('username' => ($this->user), 'password' => $this->pass)); //Set credentials and submit
    try {
        $link = $crawler->selectLink('Firm bank account')->link();
    } catch (InvalidArgumentException $e) {
     return null; 
    }
    $crawler = $client->click($link);
    $data = $crawler->filter($userNode)->each(function ( $node, $i) { //Grab the data (from the bank page)
        return $node->text();;
    });
    return $data[0];
  }
  private function getBankInfo() {
    /* Checks if credentials have been set*/
    if ($this->status == false) {
      return false; //Returns false for invaild credentials
    }

    $userNode = '#user_section > b'; //Its what is used to grab the name in HTML Scrapping

    $client = new Client(); //Create a new Goutte Client
    $crawler = $client->request('GET', 'https://portal.veinternational.org/login/'); //Request the website to visit
    $form = $crawler->selectButton('Sign in')->form(); //Find the form to login with
    $crawler = $client->submit($form, array('username' => ($this->user), 'password' => $this->pass)); //Set credentials and submit
    try {
        $link = $crawler->selectLink('Your personal bank account')->link();
    } catch (InvalidArgumentException $e) {
     return null; 
    }
    $crawler = $client->click($link);
    $data = $crawler->filter($userNode)->each(function ( $node, $i) { //Grab the data (from the bank page)
        return $node->text();;
    });
    return $data[0];
  }
  /*
    GET USERS NAME
  */
  public function getRealname() {
    $user = $this->getFirmInfo(); //Get info
    $user = explode(" (", $user); //Split at middle
    $name = $user[0]; //Set array to needed notation
    if (empty($name)) {
       $name = $this->getBankInfo(); //Get info
    }
    return $name; //Remove front character

    
  }
   /*
    GET COMPANY THE USER WORKS FOR
  */
  public function getCompany() {
    $user = $this->getFirmInfo(); //Get info
    $user = getStringAfterChar($user, "(", 0); //Strip name
    $user = explode(" - PF Code ", $user); //Split at middle
    $company = $user[0]; //Set array to needed notation

    return substr($company, 1); //Remove front character

    
  }
  /*
    GET THE ID OF THE USERS COMPANY
  */
  public function getCompanyID() { 
    $user = $this->getFirmInfo(); //Get info
    $user = getStringAfterChar($user, "(", 0); //Strip name off
    $user = explode(" - PF Code ", $user); //Split at middle
    $companyid = $user[1]; //Set array to needed notation

    return substr($companyid, 0, strlen($companyid)- 1); //Remove last character
  
  }
  
  /*
    THIS GET ALL PURCHASES BASED ON DATE RANGE AND URL ID (store manager)

    @param URL_ID is the id of the storemanager give to all firms. VVVVVVVVV
    @param Start Date is a date in this format: MONTH/DAY/YEAR eg: 09/05/16 (all numbers need to be double digits too)
    @param End Date is same as start date but can be before start date, mus be after.

    @URL-EXAMPLE w/ Random Date:
    https://portal.veinternational.org/storemanager/<<< ID IS RIGHT HERE>>>/salestransactions/xl/?sd=07/31/2016&ed=10/31/2016';

    @NOTICE
    - This is usally ran once a day, to update database and such. Keep in mind an IP Address could be block for excessive requests.
    - Also it save to a file called vei.xlsx (and may create it if not there). This happens everytime a getExcel function is request.
      meaning you should limit how much you use it. (Once per 12 hours or once per day)
  */

  public function getExcelDataCustom($url_id, $start_date, $end_date) {
    /* Checks if credentials have been set*/
    if ($this->status == false) {
      return false; //Returns false for invaild credentials
    }
    $client = new Client(); //Create a new Guotte Client
    $crawler = $client->request('GET', 'https://portal.veinternational.org/login/'); //Request the website to visit
    $form = $crawler->selectButton('Sign in')->form(); //Find the form to login with
    $crawler = $client->submit($form, array('username' => ($this->user), 'password' => $this->pass)); //Set credentials and submit

    /*
      Instead of getting a realname, we want to get some file contents.
      This here gets the contents of the excel file (xlsx)

    */
    $download = 'https://portal.veinternational.org/storemanager/' . $url_id . '/salestransactions/xl/?sd=' . $start_date .'&ed=' . $end_date;
    $client->request('GET', $download);
    $data = $client->getResponse()->getContent();

    /*
      Create a temp file, and save the xlsx data to it.
    */
    $myfile = fopen("example.xlsx", "w");
    fwrite($myfile, $data);
    fclose($myfile);

    /***********************************************************/

    /*
      Create a XLSX Reader for the document
    */
    $reader = ReaderFactory::create(Type::XLSX); // for XLSX files

    $reader->open("example.xlsx"); //Set the file path (genric I know)

    $sheet_num = 0; //
    $row_num = 0;
    $data = Array();

    foreach ($reader->getSheetIterator() as $sheet) {
        $sheet_num = $sheet_num + 1;
        $row_num = 0;
        foreach ($sheet->getRowIterator() as $row) {
           $row_num = $row_num + 1;
           if ($row_num != 1) {
              if ($sheet_num == 1) {
                //->format("Y-m-d H:i:s");
                //->timezone();
                $trans = $row[0];
                $data[$trans]['dateTime'] = $row[1];
                $data[$trans]['billing_name'] = $row[2];
                $data[$trans]['billing_company'] = $row[3];
                $data[$trans]['billing_address'] = $row[4];
                $data[$trans]['billing_city'] = $row[5];
                $data[$trans]['billing_state'] = $row[6];
                $data[$trans]['billing_zip'] = $row[7];
                $data[$trans]['billing_country'] = $row[8];
                $data[$trans]['shipping_name'] = $row[9];
                $data[$trans]['shipping_company'] = $row[10];
                $data[$trans]['shipping_address'] = $row[11];
                $data[$trans]['shipping_city'] = $row[12];
                $data[$trans]['shipping_state'] = $row[13];
                $data[$trans]['shipping_zip'] = $row[14];
                $data[$trans]['shipping_country'] = $row[15];
                $data[$trans]['email'] = $row[16];
                $data[$trans]['subtotal'] = $row[17];
                $data[$trans]['shipping'] = $row[18];
                $data[$trans]['tax'] = $row[19];
                $data[$trans]['total'] = $row[20];
             }  else {
                $trans = $row[0];
                $data[$trans]['item_name'] = $row[1];
                $data[$trans]['item_number'] = $row[2];
                $data[$trans]['price'] = $row[3];
                $data[$trans]['quantity'] = $row[4];
                $data[$trans]['amount'] = $row[5];
              }
           }
        }
    }
    $reader->close();
    return $data;
  }
  /*
    YESTERDAY'S PURCHASES
    @param url_id for the portal id
  */
  public function getExcelDataYesterday($url_id) {
    $date = new DateTime();
    $date->add(DateInterval::createFromDateString('yesterday'));
    $yesterday = $date->format('m/d/Y');
    $data = $this->getExcelDataCustom($url_id, $yesterday, $yesterday);
    return $data;
  }
  /*
    TODAY'S PURCHASES
    @param url_id for the portal id
  */
   public function getExcelDataToday($url_id) {
    $date = new DateTime();
    $date->add(DateInterval::createFromDateString('today'));
    $today = $date->format('m/d/Y');
    $data = $this->getExcelDataCustom($url_id, $today, $today);
    return $data;
  }
}
?>
