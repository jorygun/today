<?php
namespace DigitalMx\jotr;

use DigitalMx as u;
use DigitalMx\jotr\Definitions as Defs;
use DigitalMx\jotr\Errors as Errors;



class DataException extends \Exception
{

	private $errors;

    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Throwable $previous = null) {
        // some code

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);

		$this->errors = new Errors ();
		$this->showResponse ($code);



    }

    // custom string representation of object


    public function customFunction() {
        echo "A custom function for this type of exception\n";
    }

    private function showResponse($code) {

    	  u\echoAlert("Data Error: " . $this->errors->getEcode($code) );
       // echo "<script>history.back();</script>";

    }
}
