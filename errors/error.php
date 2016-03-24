<?php

/**
**************************
** FreeTSP Version: 1.0 **
**************************
** http://www.freetsp.info
** https://github.com/Krypto/FreeTSP
** Licence Info: GPL
** Copyright (C) 2010 FreeTSP v1.0
** A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
** Project Leaders: Krypto, Fireknight.
**/

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?php if (isset($_GET['error']))
    {
        echo htmlspecialchars($_GET['error']);
    } ?> Error</title>
    <link rel="stylesheet" type="text/css" href="/errors/error-style.css">
</head>
<body>
<div id="container">

    <?php

    require_once 'error-config.php';

    //-- Get The Requested And The Home Page --//
    if (substr($root, -1) == '/')
    {
        $requestedPage = substr($root, 0, strlen($root) - 1).$_SERVER['REQUEST_URI'];
        $home          = substr($root, 0, strlen($root) - 1);
    }
    else
    {
        $requestedPage = $root.$_SERVER['REQUEST_URI'];
        $home          = $root;
    }

    if (!isset($_POST['notify']))
    {
        //-- Protect Against Suspicious Page Calls --//
        if (!isset($_GET['error']) || !ctype_digit($_GET['error']) | strlen($_GET['error']) != 3)
        {
            echo $suspect;
        }
        else
        {
            $error = htmlspecialchars($_GET['error']);
            switch ($error)
            {
                case "401":
                    echo $error401;
                    break;

                case "403":
                    echo $error403;
                    break;

                case "404":
                    if (isset($oldExt) and isset($newExt))
                    {
                        $error404 .= '<p><span style="font-weight:bold;">Page requested: '.$requestedPage.'</span></p>';

                        if (substr($requestedPage, -strlen($oldExt), strlen($oldExt)) == $oldExt)
                        {
                            $len        = strlen($requestedPage) - strlen($oldExt);
                            $suggestion = substr($requestedPage, 0, $len).$newExt;
                            $error404 .= '<p>Perhaps you wanted <a href="'.$suggestion.'">'.$suggestion.'</a>?</p>';
                        }
                    }
                    echo $error404;
                    break;

                case "500":
                    echo $error500;
                    break;

                default:
                    echo $suspetion;
            }
            notify($error);
            echo '<p class="home"><a href="'.$home.'">Home Page</a></p>';
        }
    }
    else
    {
        if ($email_notification == 'y')
        {
            $datetime = date('d-m-Y H:i:s');
            $subject  = $_SERVER['HTTP_HOST'].': '.$_POST['error'].' Error';
            $message  = 'Timestamp: '.$datetime."\n\n";
            $message .= "Site: ".$root."\n\n";
            $message .= "HTTP Error: ".$_POST['error']."\n\n";
            $message .= "Requested Page: ".$home.$_POST['request']."\n\n";
            $message .= "Notification sent by user logged at: ".$_POST['ip']."\n\n";

            if (mail($email, $subject, $message, $from))
            {
                $thanksMessage .= '<p><a href="'.$home.'">Home Page</a></p>';
                echo $thanksMessage;
            }
            else
            {
                echo '<p>There was a problem sending your report.</p>';
                echo '<p>Please <a href="mailto:'.$email.'">contact us</a> with details of the problem you experienced.</p>';
            }
        }
    }

    function notify ($error)
    {
        echo '<p>If you arrived at this page via a link on this site, you can also send an automated error report. Your email address will not be required.</p>';
        echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post">';
        echo '<input type="hidden" name="error" value="'.$error.'" />';
        echo '<input type="hidden" name="request" value="'.$_SERVER['REQUEST_URI'].'" />';
        echo '<input type="hidden" name="ip" value="'.$_SERVER['REMOTE_ADDR'].'" />';
        echo '<input type="submit" name="notify" value="" class="submit" />';
        echo "</form>";
    }

    ?>

</div>
</body>
</html>