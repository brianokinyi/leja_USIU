<?php
    // Try using POST with online NGROK and sandbox
/*
	$phonenumber = $_GET['MSISDN'];  
    $sessionID = $_GET['sessionId'];  
    $servicecode = $_GET['serviceCode'];  
    $ussdString = $_GET['text'];

*/
/*
    //Connet to database
    $servername = "localhost";
	$dbase_username = "root";
	$password = "";
	$dbname="leja";
*/
	//NGROK
	$user_phonenumber = $_POST['phoneNumber'];
    $phonenumber = str_replace("+", "", $user_phonenumber); 	//remove the "+" in phone number
    $sessionID = $_POST['sessionId'];  
    $servicecode = $_POST['serviceCode'];  
    $ussdString = $_POST['text'];

	//LEJA.CO.KE database
	$servername = "localhost";
	$dbase_username = "leja567_brianokinyi";
	$password = "BOOFboof999";
	$dbname="leja567_leja";

	//Current user username
	$username = "lj".$phonenumber;    

	// Create connection **MYSQLI
	$conn = new mysqli($servername, $dbase_username, $password, $dbname);

	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}

	$level =0; 

	if($ussdString != ""){  
	    $ussdString=  str_replace("#", "*", $ussdString);  
	    $ussdString_explode = explode("*", $ussdString);
	    $level = count($ussdString_explode);  
    }

    //echo ussd_text
    function ussd_proceed ($ussd_text){  
    	echo $ussd_text;  
	}
	
	//If user exists or not
    $query = "SELECT * from subscribers where phone_Number ='$phonenumber'";
	if ($result=mysqli_query($conn,$query)){
		if(mysqli_num_rows($result) > 0){
			//View my account menu			
			if ($level==0){
				displaymenu();
			}    
		    if ($level>0){  
			    switch ($ussdString_explode[0]) {  
				    case 1: //Update stock 
				    	update_purchases($ussdString_explode,$phonenumber, $username, $conn);
				    	break;  
				    case 2:  //Update sales
					    update_sales($ussdString_explode,$phonenumber, $username, $conn);  
					    break;
				    case 3:  //Profits and losses
				    	profit_losses($ussdString_explode,$phonenumber, $username, $conn);				   
				    	break; 
				    case 4: 
				    	//Loans 
				   		$ussd_text="END <br>You are not legible to receive loans. You must use Leja for at least six months.\nThank you.";  
			   			ussd_proceed($ussd_text);
				    	break;
				    case 5:
				    	//Statements
				        statements($ussdString_explode,$phonenumber, $username, $conn);
				    	break;
				    case 6:
				    	//MyAccount
				        //display_my_account_menu($ussdString_explode,$phonenumber, $username, $conn);
						getHelp();
				    	break;
				    case 0:
				    	die();
				    	break;
				    default:
				    	$ussd_text = "Invalid option";
						ussd_proceed($ussd_text);
				    	break; 
				}  //End switch
		    }  
		}
		else{
		  	//Registration menu
		  	 if ($level==0){  
			    	$ussd_text="CON \nWelcome to LEJA.\n1. Register\n2. About Leja\n3. Choose Kiswahili";  
			   		ussd_proceed($ussd_text);    
			    } 
				
				//First selection either registration or about
				if ($level>0){  
				    switch ($ussdString_explode[0])  
					    {  
					    case 1:  
						    register($ussdString_explode,$phonenumber, $conn);  
						    break;  
					    case 2:  
						    about($ussdString_explode,$phonenumber);
						    
						    break;
					    case 3:  
				   			//Change langauage
					    	require("lejaSwahili.php");
					    	die();
				    		break;
				    }//End switch  
			    }  	
			}//End else
		}
		else{
		    $ussd_text = "Query failed";
			ussd_proceed($ussd_text);
		}

	function displaymenu(){  
		$ussd_text="CON \n1: Update Purchases\n2: Update Sales\n3: Profits and Losses \n4: Loans \n5: Statements \n 6: Help \n00: Exit";  
		ussd_proceed($ussd_text);  
	}

	function displayNewMemberMenu(){
		$ussd_text = "CON \nWe are happy to see you here.<br>1. Create new order <br>00. Exit";
		ussd_proceed($ussd_text);
	}

	function display_my_account_menu($details,$phone, $active_user, $conne){
		$sql = "SELECT fisrt_Name, last_Name, id_Number, business_Type, county FROM subscribers WHERE username = '$active_user";
		$result = $conne->query($sql);
		if ($result->num_rows > 0){
			$row = fetch_assoc();
			$ussd_text = "END <br>Account Details<br>";
			$ussd_text .= $row["first_Name"]." ".$row["last_Name"]."<br>";
			$ussd_text .= "Id Number: ".$row["id_Number"]."<br>";
			$ussd_text .= "Business Type: ".$row["business_Type"]."<br>";
			$ussd_text .= "County: ".$row["county"]."<br>";
			ussd_proceed($ussd_text);
		}
	}

	function register($details,$phone, $conne){      
		if (count($details)==1){  
			$ussd_text="CON \n Enter your first name:";  
			ussd_proceed($ussd_text);  
		} 
		else if(count($details) == 2){		  
			$ussd_text = "CON Enter your last name:\n";  
			ussd_proceed($ussd_text);
		}
		else if(count($details) == 3){		  
			$ussd_text = "CON Enter your National ID number:\n";  
			ussd_proceed($ussd_text);  
		}
		else if(count($details) == 4){
			$ussd_text = "CON <br> Enter your email address";
			ussd_proceed($ussd_text);
		}
		else if(count($details) == 5){		  
			$ussd_text = "CON \n Enter your type of business:\n";  
			ussd_proceed($ussd_text);  
		}
		else if(count($details) == 6){		  
			$ussd_text = "CON \n Reply with your residential county:\n";  
			ussd_proceed($ussd_text);  
		}
		else if(count($details) == 7){  
			$fname=$details[1];
			$sname=$details[2];  
			$id_number=$details[3];
			$email = $details[4];
			$business_type=$details[5];    
			$county=$details[6];

			//Validate and sanitize
			if(!filter_var($fname, FILTER_SANITIZE_STRING) === TRUE){
				$fname = NULL;
			}
		  	if(!filter_var($sname, FILTER_SANITIZE_STRING) === TRUE){
				$sname = NULL;
			}
			if(!filter_var($id_number, FILTER_VALIDATE_INT) === TRUE){
				$id_number = NULL;
			}
			if(!filter_var($business_type, FILTER_SANITIZE_STRING) === TRUE){
				$business_type = NULL;
			}
			if(!filter_var($county, FILTER_SANITIZE_STRING) === TRUE){
				$county = NULL;
			}
			if(!filter_var($email, FILTER_SANITIZE_EMAIL) === TRUE){
				$county = NULL;
			}

			//============   Tablename    ===================
			$username = "lj".$phone;

			//=================Write into database all the details=========================== 
			$sql = "INSERT INTO subscribers (first_Name, last_Name, phone_Number, id_Number, email, business_Type, county, username, registration_Platform) 
					VALUES ('$fname', '$sname', '$phone', '$id_number', '$email', '$business_type', '$county', '$username', 'MOBILE')";
			if($conne->query($sql) == TRUE){
				create_table($phone, $conne);
				$message = "Thank you $fname for choosing Leja.\nYou are now our new member.";
				sendSMS(("+".$phone), $message);
				echo "END ".$message;
			}
			else{
				echo "error: ".$sql ."\n" .$conne->error;
			}
		}  
	}

	function create_table($phone, $conne){
    	$tableName = "lj".$phone;
    	$sql = "CREATE TABLE $tableName (
    		_date TIMESTAMP,
			id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
			purchases INT(30),
			expenditure INT(100),
			sales INT(100),
			balance INT(100)
			)";
		if ($conne->query($sql) === TRUE) {
		     //echo "Table for $tableName is successfully created";
		     $sql = "INSERT INTO $tableName (purchases,expenditure,sales,balance) VALUES ('0','0','0','0')"; 

		    if($conne->query($sql) == TRUE){
		        //echo "successfully inserted";
		    }
		    else{
		        echo "error: ".$sql ."<br>" .$conne->error;
		    }
		} 
		else {
		    echo "Error creating table: " . $conne->error;
		}
    }

    function about(){
    	$ussd_text="END <br>Leja is a USSD based inventory management system. Leja helps you keep records and make calculation of your business to ensure you convenience and profits.<br>Visit http://www.leja.co.ke to learn more";   
		ussd_proceed($ussd_text);
    }

    function update_purchases($details,$phone, $active_user, $conne){
	    if (count($details)==1){  
		    $ussd_text="CON \nEnter value of the goods you have bought today: ";  
		    ussd_proceed($ussd_text);  
	    }  
	    else if (count($details)==2){  
		    $ussd_text="CON \nExtra expenditure on goods(eg. transportation)";  
		    ussd_proceed($ussd_text);  
	    }  

	    else if(count($details) == 3){  
		    $purchases=$details[1];  
		    $expenditure=$details[2];

		    //Validate data
		    if(filter_var($purchases, FILTER_VALIDATE_INT) === FALSE){
		    	$purchases = NULL;
		    }
		    if(filter_var($expenditure, FILTER_VALIDATE_INT) === FALSE){
		    	$expenditure = NULL;
		    } 

		$sql = "SELECT  balance FROM $active_user ORDER BY id DESC LIMIT 1";
		$result = $conne->query($sql);
		if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				//Calculations
			    $t_bal = $row["balance"] + $purchases + $expenditure;
			} else {
			    echo "0 results";
			}
	      
		    $sql = "INSERT INTO $active_user (purchases,expenditure,balance) VALUES ('$purchases','$expenditure','$t_bal')"; 

		    if($conne->query($sql) == TRUE){
		        $ussd_text="END \nYou have successfully recorded your purchases";  
		    	ussd_proceed($ussd_text);
		    }
		    else{
		        echo "error: ".$sql ."<br>" .$conne->error;
		    }
	    }  
	}

	function update_sales($details,$phone, $active_user, $conne){
	     if (count($details)==1){  
		    $ussd_text="CON \nEnter the total value of all goods sold today";  
		    ussd_proceed($ussd_text);  
	    }   

	    else if(count($details) == 2){  
		    $sales=$details[1]; 

		     //Validate data
		    if(filter_var($sales, FILTER_VALIDATE_INT) === FALSE){
		    	$purchases = NULL;
		    }

		$sql = "SELECT  balance FROM $active_user ORDER BY id DESC LIMIT 1";
		$result = $conne->query($sql);
		if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				//calculations
			    $t_bal = $row["balance"] - $sales;
			} else {
			   // echo "0 results";
			}	      
		    $sql = "INSERT INTO $active_user (sales,balance) VALUES ( '$sales','$t_bal')"; 

		    if($conne->query($sql) == TRUE){
		    	$ussd_text="END \nYou have successfully recorded your sales";  
		    	ussd_proceed($ussd_text);
		    }
		    else{
		        echo "error: ".$sql ."<br>" .$conne->error;
		    }
	    }  
	}

	function profit_losses($details,$phone, $active_user, $conne){
		$sql = "SELECT balance FROM $active_user ORDER BY id DESC LIMIT 1";
		$result = $conne->query($sql);
		if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				//calculations
			    $t_bal = $row["balance"];
			    if($t_bal > 0){
			    	$ussd_text="END \nYou have a LOSS of: $t_bal. Sell more products and earn.";  
		   			ussd_proceed($ussd_text);
			    }elseif ($t_bal < 0){
			    	$ussd_text="END \nYou have a PROFIT of: ".-1*$t_bal."Keep selling your goods and earn more";  
		   			ussd_proceed($ussd_text);
			    }else{
			    	$ussd_text = "END \nYour profit and loss is balanced";
			    	ussd_proceed($ussd_text);
			    }
			} else {
				$ussd_text = "No results";
				ussd_proceed($ussd_text);
		}
	}

	function statements($details,$phone, $active_user, $conne){
		//Fetch email address of user
		$sql = "SELECT * FROM subscribers WHERE phone_Number = '$phone'";

		$result = $conne->query($sql);
		if(!$result){
			$ussd_text = "END Ooops!<br>
						We cannot find your email. $phone
					";
			ussd_proceed($ussd_text);
        }else{
        	$row=mysqli_fetch_array($result);
        	$to = $row['email'];
        }
		
		$sql = "SELECT * FROM $active_user ORDER BY id";
		$result =$conne->query($sql);
		if(!$result)
		{
			$ussd_text = "END No results";
			ussd_proceed($ussd_text);
		}

		$no = 1;
		$total_sales = 0;
		$total_purchases = 0;
		$total_expenditure = 0;
		
		//mail to:
		$subject = "Statements for your business";

		$message = "<html><body>";
		$message .= '<h3 style="margin: 1em 0 0.5em 0;color: #788699;font-size: 22px;line-height: 40px;font-weight: normal;text-transform: uppercase;font-family: Orienta, sans-serif;letter-spacing: 1px;font-style: italic;">
				Here is your full statement
				</h3>';
		$message .= '<table style="font-family: verdana,arial,sans-serif;font-size:11px;color:#333333;border-width: 1px;border-color: #999999;border-collapse: collapse;">
						<tr>
							<th style="background:#b5cfd2;border-width: 1px;padding: 8px;border-style: solid;border-color: #999999;">Date</th>
							<th style="background:#b5cfd2;border-width: 1px;padding: 8px;border-style: solid;border-color: #999999;">Purchases</th>
							<th style="background:#b5cfd2;border-width: 1px;padding: 8px;border-style: solid;border-color: #999999;">Expenditure</th>
							<th style="background:#b5cfd2;border-width: 1px;padding: 8px;border-style: solid;border-color: #999999;">Sales</th>
							<th style="background:#b5cfd2;border-width: 1px;padding: 8px;border-style: solid;border-color: #999999;">Balance</th>
						</tr>
				';
		
		while ($row=mysqli_fetch_array($result)){		
			$message .= '
				<tr>
					<td style="background:#F5F5F5;border-width: 1px;padding: 8px;border-style: solid;border-color: #999999;">'.$row['_date'].'</td>
		            <td style="background:#F5F5F5;border-width: 1px;padding: 8px;border-style: solid;border-color: #999999;">'.$row['purchases'].'</td>
		            <td style="background:#F5F5F5;border-width: 1px;padding: 8px;border-style: solid;border-color: #999999;">'.$row['expenditure'].'</td>
		            <td style="background:#F5F5F5;border-width: 1px;padding: 8px;border-style: solid;border-color: #999999;">'.$row['sales'].'</td>
		            <td style="background:#F5F5F5;border-width: 1px;padding: 8px;border-style: solid;border-color: #999999;">'.$row['balance'].'</td>
	           	</tr>';
           	$total_purchases += $row['purchases'];
           	$total_expenditure += $row['expenditure'];
           	$total_sales += $row['sales'];
            $no++;
			}//End while

		$message .= "</table>";
		$message .= "</body></html>";

		$message .= "<br>";
		$message .= '<h3 style="color: lightgrey;">Grand totals</h3>';
		$message .="Total sales:".$total_sales."<br>";
		$message .="Total purchases:".$total_purchases."<br>";
		$message .="Total Expenditure;".$total_expenditure."<br>";
		$message .="Net Total:".($total_sales-($total_expenditure + $total_purchases))."<br>"; 

		// Set content-type when sending HTML email
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

		// More headers
		$headers .= 'From: Leja Business Statements <statements@leja.co.ke>' . "\n";
		$headers .= "Return-Path: statements@leja.co.ke\n"; // Return path for errors
		$headers .= "Content-type: text/html; charset=iso-8859-1";
		$headers .= 'X-Mailer: PHP/' . phpversion();

		$retval = mail($to,$subject,$message,$headers);
		

		//Also send a message
		$clean_message = "LEJA MINI-STATEMENT"."\n";
		
		while ($row=mysqli_fetch_array($result)){		
			$clean_message .= '
					$row[_date]
					$row[purchases]
					$row[expenditure]
					$row[sales]
					$row[balance]
					';
           	$total_purchases += $row['purchases'];
           	$total_expenditure += $row['expenditure'];
           	$total_sales += $row['sales'];
            $no++;
		}//End while

		$clean_message .="Total sales:".$total_sales."\n";
		$clean_message .="Total purchases:".$total_purchases."\n";
		$clean_message .="Total Expenditure;".$total_expenditure."\n";
		$clean_message .="Net Total:".($total_sales-($total_expenditure + $total_purchases))."\n"; 

		// Send sms
		sendSMS($phone, $clean_message);

		if($retval == TRUE){
			$ussd_text = "END We have sent you an SMS with your statement and another copy to your email.";
			ussd_proceed($ussd_text);
		}else{
			$ussd_text = "END Ooops!<br>
							We encountered an error while fetching your statements. <br>Kindly contact the administrator.
						";
			ussd_proceed($ussd_text);
		}

		$conne->close();
	}

	function sendSMS($recepient, $message){
		require_once('AfricasTalkingGateway.php');

		$username   = "brianokinyi";
		$apikey     = "1c5223686d70be30004883b4be54a7c3fa29a063d0716ba2d7164ece2434e409";

		$gateway    = new AfricasTalkingGateway($username, $apikey);

		try 
		{
		  $results = $gateway->sendMessage($recepient, $message);
		}
		catch ( AfricasTalkingGatewayException $e )
		{
			//echo "Encountered an error while sending: ".$e->getMessage();
		}
	}

	function getHelp() {
		$ussd_text="END \nVisit www.leja.co.ke to view FAQs";  
		ussd_proceed($ussd_text);
	}
?>