<?php

class SignupForm
{
    public $outfile = "signups.txt";
    public $username_string = "";
    
    //parse their username
    public function parse_username($username)
    {
    	echo "Parsing username...\n";
    	$this->username_string = "Username: " . $username;
    	
    	//TODO: remove swear words from the username >:(
    }

    //do this after everything else is finished
    public function __destruct()
    {
    	//write the username to file
    	file_put_contents(__DIR__ . '/' . $this->outfile, $this->username_string);
    	echo "Added your username to the signups file!\n";
    }
}

//get their username from the request parameter
$username_input = $_GET['username'];

//unpack the data so we can use it later!
//TODO: check this is safe - I think I saw a Medium article somewhere saying not to do it
$unserialised = unserialize($username_input);

$signup_handler = new SignupForm;
$signup_handler->parse_username($username_input);

?>
