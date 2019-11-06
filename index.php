<?php

// allegedly can be ran on a browser as: "script.php?p=00100100"
// can be run on the command line as: C:\Users\matthewp\Sync\Code - Programming\PHP> php jsonTest.php -p00100100 or php jsonTest.php -p'Bank Select'

//Include database setup file
include 'AzureConnect.php';

$numArgs = 0; //Will store the number of input arguments

//Get input arguements
// $val['p'] = input value
$val = getopt("p:");
if ($val !== false) 
{
	$numArgs = count($val);
}
else 
{
	echo "Could not get value of command line option\n";
}

//If we have input arguements, then lets run a query
if($numArgs > 0)
{
	$input = $val['p'];
	$isBinary = false;

	if ( preg_match('~^[01]+$~', $input) ) 
	{
	    $isBinary = true;
	}

	$decimalValue = bindec($input);

	//create sql statement based on input type
	if($isBinary)
	{
		if($decimalValue > 127)
		{
			$sql = "SELECT statusFunction
					FROM mididb.statusbytes
					WHERE binaryValue = '{$input}'";
		}
		else
		{
			$sql = "SELECT controlFunction
					FROM mididb.controlandmodechanges
					WHERE binaryValue = '{$input}'";
		}
	}
	else
	{
		$sql = "SELECT binaryValue
				FROM mididb.controlandmodechanges
				WHERE controlFunction = '{$input}'";
	}

	// Check if there are results
	if ( $result = mysqli_query($con, $sql) )
	{
	 // If so, then create a results array and a temporary one
	 // to hold the data
	 $resultArray = array();
	 $tempArray = array();
	 
	 // Loop through each row in the result set
	 while($row = $result->fetch_object())
	 {
	 // Add each row into our results array
	 $tempArray = $row;
	     array_push($resultArray, $tempArray);
	 }
	 
	 // Finally, encode the array to JSON and output the results
	 echo json_encode($resultArray);
	}
}
else
{
	echo "No input provided.\n";
}
 
// Close connections
mysqli_close($con);

?>