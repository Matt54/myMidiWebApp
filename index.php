<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
// can be run on browser as: https://mymidiwebapp.azurewebsites.net/index.php?p=00010000
// can be run on the command line as: C:\Users\matthewp\Sync\Code - Programming\PHP> php jsonTest.php -p00100100 or php jsonTest.php -p'Bank Select'

//Include database setup file
include 'AzureConnect.php';

$hasArguments = false;
$bNumArgs = 0; //Will store the number of input binary arguments
$lNumArgs = 0; //Will store the number of input list arguments

//Get value from command line if present
$bVal = getopt("b:");
if ($bVal !== false) 
{
	$bNumArgs = count($bVal);
	if($bNumArgs > 0) $bInput = $bVal['b'];
}
$lVal = getopt("l:");
if ($lVal !== false) 
{
	$lNumArgs = count($lVal);
	if($lNumArgs > 0) $lInput = $lVal['l'];
}

//Get value from web browser if present
if(isset($_GET["b"])) 
{
	$bInput = $_GET["b"];
	$bNumArgs = 1;
}
if(isset($_GET["l"])) 
{
	$lInput = $_GET["l"];
	$lNumArgs = 1;
}

//Flag if we got any arguements
if($bNumArgs + $lNumArgs > 0) $hasArguments = true;

//If we have input arguements, then lets run a query
if($hasArguments)
{
	$sql="";
	if($bNumArgs > 0)
	{
		
		$isBinary = false;

		if ( preg_match('~^[01]+$~', $bInput) ) 
		{
		    $isBinary = true;
		}

		$decimalValue = bindec($bInput);

		//create sql statement based on input type
		if($isBinary)
		{
			if($decimalValue > 127)
			{
				$sql = "SELECT statusFunction
						FROM mididb.statusbytes
						WHERE binaryValue = '{$bInput}'";
			}
			else
			{
				$sql = "SELECT controlFunction
						FROM mididb.controlandmodechanges
						WHERE binaryValue = '{$bInput}'";
			}
		}
		else
		{
			$sql = "SELECT binaryValue
					FROM mididb.controlandmodechanges
					WHERE controlFunction = '{$bInput}'";
		}
		runQuery($con, $sql);
	}
	if($lNumArgs > 0)
	{
		switch($lInput)
		{
			// list all synth models with their respective manufacturer
			case "m":
				$sql = "SELECT synths.synthname, manufacturerid.name
					FROM mididb.synths, mididb.manufacturerid
					WHERE synths.manufacturerid = manufacturerid.id";
				break;

			// list all status bytes
			case "StatusBytes":
				$sql = "SELECT * FROM mididb.statusbytes";
				break;

			// list all control and mode change bytes
			case "ControlAndModeChanges":
				$sql = "SELECT * FROM mididb.controlandmodechanges";
				break;

			case "SynthTableNames":
				$sql = "SELECT synthTableName FROM mididb.synths WHERE synthTableName IS NOT NULL";
				break;
			case "NordStage3":
				$sql = "SELECT * FROM mididb.nordstage3";
				break;
			case "e":
				$sql = "SELECT manufacturername,
							   byte1binaryvalue,
							   byte2binaryvalue, 
							   byte3binaryvalue FROM mididb.ManufacturerSysExID";
				break;
		}
		runQuery($con, $sql);
	}
	
}
else
{
	echo "No input provided.\n";
}

function runQuery($con, $sql)
{
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
	 echo json_encode($resultArray, JSON_PRETTY_PRINT);
	}
}
 
// Close connections
mysqli_close($con);

?>