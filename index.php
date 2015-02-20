<?php

/**
 * @package Retrieve Your Emails from any mail server Using PHP and IMAP
 * @author Ind(_thinkholic) | 2015-02-20
 *
 * @credits http://php.net/manual/en/function.imap-open.php
 * @credits http://davidwalsh.name/gmail-php-imap
 */

$authhost="{pop.gmail.com:995/pop3/ssl/novalidate-cert}"; // Google POP3

## localhost POP3 with and without SSL
#  $authhost="{localhost:995/pop3/ssl/novalidate-cert}";
#  $authhost="{localhost:110/pop3/notls}";

## localhost IMAP with and without SSL
#  $authhost="{localhost:993/imap/ssl/novalidate-cert}";
#  $authhost="{localhost:143/imap/notls}";

## localhost NNTP with and without SSL
## you have to specify an existing group, control.cancel should exist
#  $authhost="{localhost:563/nntp/ssl/novalidate-cert}control.cancel";
#  $authhost="{localhost:119/nntp/notls}control.cancel";

## web.de POP3 without SSL
#  $authhost="{pop3.web.de:110/pop3/notls}";

## Goggle with POP3 or IMAP
#  $authhost="{pop.gmail.com:995/pop3/ssl/novalidate-cert}";
#  $authhost="{imap.gmail.com:993/imap/ssl/novalidate-cert}";

$user="user-id@gmail.com";
$pass="gmail-password";

$con = false; 

if ($mbox=imap_open( $authhost, $user, $pass ))
{
    $con = true;
    $has_msg = false;

    $emails = imap_search($mbox,'ALL');

    if($emails)
    {
        $has_msg = true;
        
        /* begin output var */
        $email_data = array();

        /* put the newest emails on top */
        rsort($emails);

        /* for every email... */
        $i=1;
        foreach($emails as $email_number)
        {
            /* get information specific to this email */
            $overview = imap_fetch_overview($mbox,$email_number,0);
            $message = imap_fetchbody($mbox,$email_number,2);
            
            /* output the email header information */
            $email_data[$i]['uid'] = $overview[0]->uid;
            $email_data[$i]['msgno'] = $overview[0]->msgno;
            $email_data[$i]['seen'] = $overview[0]->seen;
            $email_data[$i]['subject'] = $overview[0]->subject;
            $email_data[$i]['from'] = $overview[0]->from;
            $email_data[$i]['date'] = $overview[0]->date;
            $email_data[$i]['message'] = $message;
            
            $i++;
        }
    }
    
    imap_close($mbox);
} 

?>

<!DOCTYPE html>
<html lang="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retrieve Your Emails from any mail server Using PHP and IMAP</title>
    <link rel="stylesheet" href="">
    <style>
        body {font-family:Consolas; font-size:11px;}
    </style>
</head>

<body>
    <script src=""></script>
    <pre>
    
        <?php if ( $con && $has_msg ) { ?>
        
            <?php print_r($email_data); ?>
        
        <?php } else { ?>
        
            <p>Not Connected.</p>
        
        <?php } ?>
    
    </pre>
    
</body>
</html>