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

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'functions'.DIRECTORY_SEPARATOR.'function_main.php');
require_once(FUNC_DIR.'function_user.php');
require_once(FUNC_DIR.'function_vfunctions.php');
require_once(FUNC_DIR.'function_bbcode.php');
require_once(FUNC_DIR.'function_page_verify.php');

db_connect(false);
logged_in();

parked();

$newpage = new page_verify();
$newpage->create('_sendmessage_');

$receiver = 0 + $_GET["receiver"];

if (!is_valid_id($receiver))
{
    die;
}

$replyto = isset($_GET["replyto"]) ? (int) $_GET["replyto"] : 0;

if ($replyto && !is_valid_id($replyto))
{
    die;
}

$res = sql_query("SELECT *
                    FROM users
                    WHERE id = $receiver") or die(mysql_error());

$user = mysql_fetch_assoc($res);

if (!$user)
{
    die("No User with that ID.");
}

if ($replyto)
{
    $res = sql_query("SELECT *
                        FROM messages
                        WHERE id = $replyto") or sqlerr();

    $msga = mysql_fetch_assoc($res);

    if ($msga['receiver'] != $CURUSER['id'])
    {
        die;
    }

    $res = sql_query("SELECT username
                        FROM users
                        WHERE id={$msga['sender']}") or sqlerr();

    $usra = mysql_fetch_assoc($res);
    $body .= "\n\n\n-------- $usra[username] wrote: --------\n$msga[msg]\n";
    $subject = "Re: ".htmlspecialchars($msga['subject']);
}

site_header("Send Message", false);
?>
<table class='main' border='0' width='100%' cellspacing='0' cellpadding='0'>
    <tr>
        <td class='embedded'>
            <div align='center'>
                <h1>Message to <a href='userdetails.php?id=<?php echo $receiver?>'><?php echo $user["username"]?></a>
                </h1>

                <form name='compose' method='post' action='takemessage.php'>
                    <table border='1' cellspacing='0' cellpadding='5'>
                        <tr>
                            <td class='std' colspan='2'><span style='font-weight:bold;'><label for='subject'>Subject:&nbsp;&nbsp;</label></span>
                                <input type="text" name="subject" id='subject' size="76" value="<?php echo isset($subject) ? htmlentities($subject, ENT_QUOTES) : ''?>" />
                            </td>
                        </tr>
                        <tr>
                            <td<?php echo $replyto ? " colspan='2'" : ""?>>
                                <?php echo("".textbbcode("compose", "msg", "$body")."");?>
                            </td>
                        </tr>

                        <?php if ($replyto)
                    { ?>

                        <tr>
                            <td class='std' align='center'>
                                <input type='checkbox' name='delete' value='yes' <?php echo $CURUSER['deletepms'] == 'yes' ? "checked='checked'" : ""?> />Delete Message you are Replying to
                                <input type='hidden' name='origmsg' value='<?php echo $replyto?>' />
                            </td>
                        </tr>

                        <?php } ?>

                        <tr>
                            <td class='std' align='center'>
                                <input type='checkbox' name='save' value='yes' <?php echo $CURUSER['savepms'] == 'yes' ? "checked='checked'" : ""?> />Save Message to Sentbox
                            </td>
                        </tr>
                        <tr>
                            <td <?php echo $replyto ? " colspan='2'" : ""?> align='center'>
                                <input type='submit' class='btn' value="Send it!" />
                            </td>
                        </tr>
                    </table>
                    <input type='hidden' name='receiver' value='<?php echo $receiver?>' />
                </form>
            </div>
        </td>
    </tr>
</table>

<?php

site_footer();

?>