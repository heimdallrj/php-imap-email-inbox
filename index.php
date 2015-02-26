<?
/**
 * PHP IMAP
 * Retrieve Your Emails from any mail server Using PHP and IMAP
 *
 * @package php-imap-email-inbox
 * @author Ind(_thinkholic) | 2015-02-20/25
 *
 * @credits http://php.net/manual/en/function.imap-open.php/
 * @credits http://davidwalsh.name/gmail-php-imap/
 * @credits http://www.backslash.gr/content/blog/webdevelopment/8-check-your-email-with-php-and-imap/
 * @credits http://www.codediesel.com/php/downloading-gmail-attachments-using-php/
 *
 */

# Page Auth
$pgAuth['user'] = 'admin';
$pgAuth['password'] = 'thinkholic';

if ($pgAuth['user'] && $pgAuth['password'])
{
	if(($_SERVER["PHP_AUTH_USER"]!==$pgAuth['user']) || ( $_SERVER["PHP_AUTH_PW"]!==$pgAuth['password']))
	{
		header("WWW-Authenticate: Basic realm=Protected area" );
		header("HTTP/1.0 401 Unauthorized");
		print "::PROTECTED::";
		exit;
	}
}

# IMAP Configs
/**
 * --localhost POP3 with and without SSL--
 * "{localhost:995/pop3/ssl/novalidate-cert}"
 * "{localhost:110/pop3/notls}"
 *
 * --localhost IMAP with and without SSL--
 * "{localhost:993/imap/ssl/novalidate-cert}"
 * "{localhost:143/imap/notls}"
 *
 * --localhost NNTP with and without SSL--
 * --you have to specify an existing group, control.cancel should exist--
 * "{localhost:563/nntp/ssl/novalidate-cert}control.cancel"
 * "{localhost:119/nntp/notls}control.cancel"
 *
 * --web.de POP3 without SSL--
 * "{pop3.web.de:110/pop3/notls}"
 *
 * --Goggle with POP3 or IMAP--
 * "{pop.gmail.com:995/pop3/ssl/novalidate-cert}"
 * "{imap.gmail.com:993/imap/ssl/novalidate-cert}"
 */

$mailBoxes = array(
	array(
		'label' 	=> 'Gmail',
		'enable'	=> true,
		'mailbox' 	=> '{imap.gmail.com:993/imap/ssl}INBOX',
		'username' 	=> '<your-mail-id@google-powered-email-domain.com>',
		'password' 	=> '<password>'
	),
	array(
		'label' 	=> '<ANOTHER E-MAIL>',
		'enable'	=> false,
		'mailbox' 	=> '{mail.yourdomain.com:143/notls}INBOX',
		'username' 	=> '',
		'password' 	=> ''
	)
);

# Custom-fuctions
/**
 * Decode MIME message header extensions and get the text
 */
function decodeIMAPText( $str )
{
    $op = '';
    $decode_header = imap_mime_header_decode($str);
    
	foreach ($decode_header AS $obj)
	{
        $op .= htmlspecialchars(rtrim($obj->text, "\t"));
	}
    
	return $op;
}
?>
<!DOCTYPE html>
<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>Retrieve Your Emails from any mail Server Using PHP and IMAP</title>

<style>

	body {font-family:Consolas !important; font-size:12px; background-image:none !important; background-color:#C7C7B2 !important;}
	
	h1, h3 {font-family:Consolas !important; text-align:center !important;}
	
	div#wrapper {width:900px; margin:auto;}
	
	div.email-item {background: #fff; border-radius: 15px; margin: 25px 0; padding: 10px 25px;}
	
</style>

</head>

<body>
	
    <h1>Retrieve Your Emails from any mail Server<br/>Using PHP and IMAP</h1>

	<div id="wrapper">
    
    	<? if (!count($mailBoxes)) { ?>
        
        	<p>::INVALID_CONFIG::</p>
        
        <? } else { ?> 
        
        	<?php foreach ($mailBoxes as $curMailBox) { ?>
            
            	<h3><?php print $curMailBox['label']; ?></h3>
        
                <div class="mailBox-wrap">
                
                	<?php 
					if (!$curMailBox['enable'])
					{
                    	print '<p>::DISABLED::</p>';
					}
                    else
					{ 
						// IMAP Open
						$inbox = @imap_open($curMailBox['mailbox'], $curMailBox['username'], $curMailBox['password']); 
						
						if ( $inbox )
						{
							// Get the last week's emails only
							// Instead of searching for this week's message you could search for all the messages in your inbox using: $emails = imap_search($inbox,'ALL');
							$emails = imap_search($inbox, 'SINCE '. date('d-M-Y',strtotime("-1 week")));
							
							if (!count($emails))
							{
								print '<p>No E-mails found.</p>';
							}
							else
							{
								// Sort
								rsort($emails);
								
								foreach($emails as $email)
								{
									// Fetch the email's overview and show subject, from and date. 
									$overview = imap_fetch_overview($inbox,$email,0);
									
									// Message Body
									$message = imap_fetchbody($inbox,$email,'1.2');
									if ( trim($message) == '' )
									{
										$message = imap_fetchbody($inbox,$email,2);
									}
									$message = quoted_printable_decode($message);
									
									if ( trim($message) == '' )
									{
										$message = imap_fetchbody($inbox,$email,1);
									}
									
									// Attachements
									$structure = imap_fetchstructure($inbox,$email);
									
									$attachments = array();
									
									if( isset($structure->parts) && count($structure->parts) ) 
									{
										for($i = 0; $i < count($structure->parts); $i++) 
										{
											$attachments[$i] = array(
												'is_attachment' => false,
												'filename' => '',
												'name' => '',
												'attachment' => ''
											);
							 
											if($structure->parts[$i]->ifdparameters) 
											{
												foreach($structure->parts[$i]->dparameters as $object) 
												{
													if(strtolower($object->attribute) == 'filename') 
													{
														$attachments[$i]['is_attachment'] = true;
														$attachments[$i]['filename'] = $object->value;
													}
												}
											}
							 
											if($structure->parts[$i]->ifparameters) 
											{
												foreach($structure->parts[$i]->parameters as $object) 
												{
													if(strtolower($object->attribute) == 'name') 
													{
														$attachments[$i]['is_attachment'] = true;
														$attachments[$i]['name'] = $object->value;
													}
												}
											}
							 
											if($attachments[$i]['is_attachment']) 
											{
												$attachments[$i]['attachment'] = imap_fetchbody($inbox, $email, $i+1);
							 
												/* 4 = QUOTED-PRINTABLE encoding */
												if($structure->parts[$i]->encoding == 3) 
												{ 
													$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
												}
												/* 3 = BASE64 encoding */
												elseif($structure->parts[$i]->encoding == 4) 
												{ 
													$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
												}
											}
										}
									}
									
									// Iterate through each attachment and save it 
									foreach($attachments as $attachment)
									{
										if($attachment['is_attachment'] == 1)
										{
											$filename = $attachment['name'];
											if(empty($filename)) $filename = $attachment['filename'];
							 
											if(empty($filename)) $filename = time() . ".dat";
							 
											/* prefix the email number to the filename in case two emails
											 * have the attachment with the same file name.
											 */
											$fp = fopen($email . "-" . $filename, "w+");
											fwrite($fp, $attachment['attachment']);
											fclose($fp);
										}
							 
									}
									
									?>
									
                                    <div class="email-item clearfix <?=$overview[0]->seen?'read':'unread'?>">
                                    
                                    	<div class="meta"> 
                                          
                                            <p class="subject" title="<?=decodeIMAPText($overview[0]->subject)?>"><b>Subject:</b> <?=decodeIMAPText($overview[0]->subject)?></p>
                                            <p class="from" title="<?=decodeIMAPText($overview[0]->from)?>"><b>From:</b> <?=decodeIMAPText($overview[0]->from)?></p>
                                            <p class="date"><b>Date:</b> <?=$overview[0]->date?></p>
                                            
                                      	</div> <!--/.meta-->
                                        
                                        <div class="message">
                                        	<?=$message?>
                                        </div> <!--/message-->
                                        
                                    </div>
                                    
									<?php
								}
							}
                            
                            imap_close($inbox); 
						}
						else
						{
							print '<p>::CONNECTION_ERROR:: '. imap_last_error().'</p>';
						}
					}
					?>
                
                </div> <!--/mailBox-wrap-->
                
         	<?php } ?>
    
    	<?php } ?>

	</div> <!-- /wrapper -->
    
</body>
</html>