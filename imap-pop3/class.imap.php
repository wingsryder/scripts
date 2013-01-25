<?php
/**
* Class used to connect IMAP/POP3 account and fetch emails header on basis 
* of search date filter and sender email address.
*
* @package    imap-pop3
* @subpackage imapbox
* @author     Vikram Thakur <vikramjeet.thakur@gmail.com>
* @version    0.2-BETA
* 
* Usage:
*  To connect to a POP3 server on port 110 on the local server, use:
*  $mbox = new imapbox("{localhost:110/pop3}INBOX", "user", "pass" ,DEBUG); DEBUG = false[default] / true
* 
*  To connect to an SSL IMAP or POP3 server
*  $mbox = imap_open ("{localhost:993/imap/ssl}INBOX", "user", "password") 
* 
*  To connect to an SSL IMAP or POP3 server with a self-signed certificate,add /ssl/novalidate-cert after the protocol specification:
*  $mbox = imap_open ("{localhost:995/pop3/ssl/novalidate-cert}", "user", "password")
*  $mbox = imap_open ("{localhost:993/imap/ssl/novalidate-cert}INBOX", "user", "password")
* 
* 
*  Class calling usage example for IMAP using SSl eg: for google
*  $mbox = new imapbox("host","user","pass", DEBUG);  DEBUG = false[default] / true  
*
*  eg:
*  $mbox = new imapbox("{imap.googlemail.com:993/imap/ssl}INBOX","username@gmail.com","password",TRUE);
*  $val = $mbox->getHeaderInfo();
*  echo "<pre>";
*  print_r($val);
*  echo "</pre>";
* 
*/



define("INBOX_EMPTY", "No emails in mailbox");
define("CONNECTION_FAILED", "Connection to mail server failed");
define("CLOSING_CONNECTION","Closing mailbox connection");

// configurable parameters
define("EMAIL_INTERVAL","-10 days");
define("SEARCH_EMAIL_FROM","emailfromid@gmail.com");




class imapbox{

  private $imapHost;
  private $imapUser;
  private $imapPass;
  private $imapMbox;
  private $DEBUG;

  public function __construct($host,$user,$pass,$debug = FALSE){
	$this->imapHost = $host;
	$this->imapUser = $user;
	$this->imapPass = $pass;
        $this->DEBUG = $debug;
        
        try{
           // connect to email server in read only mode. 
	   $this->imapMbox = imap_open ($this->imapHost, $this->imapUser, $this->imapPass,OP_READONLY);
           if($this->DEBUG) echo 'connected to mail server <br>';           
        }catch(Exception $e){
           //$this->logMessg($e->getMessage());
           $this->logMessg(CONNECTION_FAILED);
        }
     	
  }

  
  public function __destruct() {
       echo CLOSING_CONNECTION." \n";
       if($this->imapMbox)
       imap_close($this->imapMbox);
  }

  
  public function logMessg($mesg){
	echo '<b color="red">'.$mesg. "</b>\n \n";
        exit;  
  }	 

  
  // count total number of message in the INBOX , we are checking ony inbox or change value in above connection.
  public function getCount(){
       $mailcount = imap_check($this->imapMbox);
       return $mailcount->Nmsgs;
  }


  public function getHeaderInfo(){

	// no email in inbox
        if($this->DEBUG) echo 'Total emails in account are '. $this->getCount().'<br>';           
	if($this->getCount() == 0){ $this->logMessg(INBOX_EMPTY);}
        
        // Find UIDs of messages within the past day/week
        $date = date( "d-M-Y", strToTime ( EMAIL_INTERVAL ) );
        $string = "FROM ".SEARCH_EMAIL_FROM." SINCE ". $date;
       
        if($this->DEBUG) echo 'Search condition : '. $string.'<br>';           

        $getMUID   = imap_search($this->imapMbox, $string);
        
        // print matching records UID's as per query
        if($this->DEBUG) print_r($getMUID);

        if(count($getMUID) == 0){ $this->logMessg(INBOX_EMPTY); }

         // loop all the available number of messages.
         $email =array();
         for($i = 1 ; $i < count($getMUID) ; $i++){
             $result = imap_headerinfo($this->imapMbox,$getMUID[$i]);
             $email[]=$result;
          }
          
        return $email;    
  }


} // class ends



?>
