<?php
//No Need to Update Below
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recaptcha_response'])) 
{
    // Build POST request:
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_response = $_POST['recaptcha_response'];

    // Make and decode POST request:
    $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $recaptcha = json_decode($recaptcha);


    // echo "hello 1 <br>";
    // echo "recaptcha->score " . $recaptcha->score . "<br>";
    // echo "hello 2 <br>";
    // Take action based on the score returned:
    if (isset($recaptcha->score) && $recaptcha->score >= 0.5)
    {

        // connect to database
        include('config.php');

               
        //Adding IP & Date
        array_push($allArray,  
            array(
                "data" => getUserIpAddr(),
                "field" => "ip_address",
                "title" => "IP Address"),
            array(
                "data" =>  date('Y-m-d H:i:s'),
                "field" => "created_at",
                "title" => "Processed on")
            );
        
        //STORE IN DB
        //CHECK IF email is already registered
        $max =  count($allArray);
        $checkQuery = "select * from " .$table. " where email='" . $_POST['email']. "'";
        $emailAlreadyRegistered = $connection->query($checkQuery);
        if(!$emailAlreadyRegistered->num_rows){
            // Construct query string
            $query = "insert into " . $table . "(";

            for($i = 0; $i < $max; $i++){
                $query .= $allArray[$i]["field"] . ", ";
            }

            $query = substr($query, 0, -2) . ") values('";

            for($i = 0; $i < $max; $i++){
                $query .= $allArray[$i]["data"] . "', '";
            }

            $query = substr($query, 0, -3) . ")";
            $db =	mysqli_query($connection, $query)  or die(mysqli_error($connection));

            // Sync with Mailchimp
            require('mailchimp-register.php');

            syncToMailchimp($_POST['email'], $_POST['first_name'], $_POST['last_name'],$_POST['phone'],$_POST['are_you_a_realtor'],$_POST['postal_code'],$_POST['are_you_working_with_realtor'],$database,$tag);
            


        }
        mysqli_close($connection);
        

        // SEND NOTIFICATION TO ADMIN
        $fd = fopen($template,"r") or die("Unable to open file!");;
        $MESSAGE = fread($fd, filesize($template));

        $fields = "<tr><td align='right' class='field'>Date Received:</td>\n";
        $fields .= "<td align='left' class='value'>" . date("M d Y",time()) . "</td></tr>\n";

        for($i = 0; $i < $max; $i++){
            $fields .= "<tr><td align='right' class='field'>" .$allArray[$i]["title"] . ": </td>\n";
            $fields .= "<td align='left' class='value'>" .$allArray[$i]["data"] . "</td></tr>\n";
        }

        $original = array("{site_name}", "{tbody}");
        $replace = array($site,  $fields);
        $MESSAGE = str_replace($original,$replace, $MESSAGE);


        // --------------------------------------------
        // send notifidcatios code starts
        // --------------------------------------------

        $emailAddAll = "";
        $recNameAll = "";

        foreach($notifyAlsoTo as $emailAdd => $recName)
        {

            $emailAddAll = $emailAddAll . $emailAdd . ",";
            $recNameAll = $recNameAll . $recName . ",";

        }

        $emailAddAll = substr($emailAddAll, 0, -1);
        $recNameAll = substr($recNameAll, 0, -1);

        //$emailSentFrom = "info@townmanors.ca";
        $emailSentFromName = $site ;


        $ryan_curl_url = 'https://ryan-design.com/client-codes/send-notifications/notifications/process.php';


        $curl_fields = array(


            'project_name' => urlencode($site),
            'email_to' => urlencode($emailAddAll),
            'full_name' => urlencode($recNameAll),
            'email_body' => urlencode($MESSAGE),
            'email_alt_body' => urlencode(""),
            'email_subject' => urlencode("E-Notification:  " .  $emailSentFromName),
            'email_replyto' => urlencode($emailSentFrom),
            'email_replyto_name' => urlencode($emailSentFromName),
         //   'debug' => "yes",


        );

        $fields_string = "";

        //url-ify the data for the POST
        foreach($curl_fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $ryan_curl_url);
        curl_setopt($ch,CURLOPT_POST, count($curl_fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);

        //echo $result ;

        //close connection
        curl_close($ch);

        // --------------------------------------------
        // send notifidcatios code ENDS
        // --------------------------------------------  

/*
        $mail->isHTML(true);
        $mail->setFrom($emailSentFrom, $site);
        $mail->Subject = "E-Notification: New Registration at ".$site;
        $mail->Body    = $MESSAGE ;

        
        if(sizeof($notifyAlsoTo))
        {
            foreach($notifyAlsoTo as $emailAdd => $recName)
            {
                $mail->AddAddress($emailAdd, $recName);
            }
        }

        // $mail->send();

        if(!$mail->send()) 
        {
            $fp = fopen('mailError.log', 'a');//opens file in append mode
            fwrite($fp, 'ERROR ON '.date('l jS \of F Y h:i:s A'). " - " .$mail->ErrorInfo. "\n"); 
            fclose($fp);     
         
        } 


        fclose($fd);

*/
        //SEND AUTO RESPONDER
        if($autoresponder){
            // $mail->ClearAllRecipients();
            // $mail->isHTML(true);
            // $mail->setFrom($emailSentFrom, $site);
            // $mail->addAddress($_POST["email"]);
            // $mail->Subject = $autoresponderSubject;
            // $mail->Body = file_get_contents($autoresponder);
            // $mail->send();


            $curl_fields_responder = array(


                'project_name' => urlencode($site),
                'email_to' => urlencode($_POST["email"]),
                'full_name' => urlencode(trim($_POST["first_name"]) . " " . trim($_POST["last_name"])),
                'email_body' => urlencode($MESSAGE),
                'email_body' => urlencode(file_get_contents($autoresponder)),
                'email_alt_body' => urlencode(""),
                'email_subject' => urlencode($autoresponderSubject),
                'email_replyto' => urlencode($emailSentFrom),
                'email_replyto_name' => urlencode($emailSentFromName),
                 //   'debug' => "yes",


            );

            $fields_string = "";

            //url-ify the data for the POST
            foreach($curl_fields_responder as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
            rtrim($fields_string, '&');

            //open connection
            $ch_resonder = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch_resonder,CURLOPT_URL, $ryan_curl_url);
            curl_setopt($ch_resonder,CURLOPT_POST, count($curl_fields_responder));
            curl_setopt($ch_resonder,CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch_resonder, CURLOPT_RETURNTRANSFER, true);

            //execute post
            $result_responder = curl_exec($ch_resonder);

            //echo $result ;

            //close connection
            curl_close($ch_resonder);            
        }
        header("Location: $thankYouURL");
        //header("Location: /thank-you.html");
        exit();
    }
    else        
    {
        // Not verified
        //echo "Not verified <br>";
         header("Location: /");
    }
}
?>