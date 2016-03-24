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
require_once(FUNC_DIR.'function_commenttable.php');
require_once(FUNC_DIR.'function_bbcode.php');

$action = isset($_GET["action"]) ? $_GET["action"] : '';

db_connect(false);
logged_in();

if ($action == "add")
{
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        $torrentid = 0 + $_POST["tid"];

        if (!is_valid_id($torrentid))
        {
            error_message("error", "Error", "Invalid ID.");
        }

        $res = sql_query("SELECT name
                            FROM torrents
                            WHERE id = $torrentid") or sqlerr(__FILE__, __LINE__);

        $arr = mysql_fetch_array($res, MYSQL_NUM);

        if (!$arr)
        {
            error_message("error", "Error", "No torrent with that ID.");
        }

        $text = trim($_POST["text"]);

        if (!$text)
        {
            error_message("warn", "Warning", "Comment body cannot be empty!");
        }

        sql_query("INSERT INTO comments (user, torrent, added, text, ori_text)
                    VALUES (".$CURUSER["id"].",$torrentid, '".get_date_time()."', ".sqlesc($text).",".sqlesc($text).")");

        $newid = mysql_insert_id();

        sql_query("UPDATE torrents
                    SET comments = comments + 1
                    WHERE id = $torrentid");

        header("Refresh: 0; url=details.php?id=$torrentid&amp;viewcomm=$newid#comm$newid");
        die;
    }

    $torrentid = 0 + $_GET["tid"];

    if (!is_valid_id($torrentid))
    {
        error_message("error", "Error", "Invalid ID.");
    }

    $res = sql_query("SELECT name
                        FROM torrents
                        WHERE id = $torrentid") or sqlerr(__FILE__, __LINE__);

    $arr = mysql_fetch_assoc($res);

    if (!$arr)
    {
        error_message("error", "Error", "No torrent with ID.");
    }

    if ($CURUSER['torrcompos'] == 'no')
    {
        error_message_center("error", "Error", "Your Torrent Comment Privilage Has Been Removed.");
    }

    site_header("Add a Comment to '".$arr["name"]."'");

    print("<h1>Add a Comment to '".htmlspecialchars($arr["name"])."'</h1>\n");
    print("<form method='post' name='compose' enctype='multipart/form-data' action='comment.php?action=add'>\n");
    print("<input type='hidden' name='tid' value='$torrentid' />\n");
    print("".textbbcode("compose", "text", "$text")."");
    print("<p align='center'><input type='submit' class='btn' value='Do it!' /></p>");
    print("</form>\n");

    $res = sql_query("SELECT comments.id, text, comments.added, username, users.id AS user, users.avatar
                        FROM comments LEFT JOIN users ON comments.user = users.id
                        WHERE torrent = $torrentid
                        ORDER BY comments.id DESC
                        LIMIT 5");

    $allrows = array();

    while ($row = mysql_fetch_assoc($res))
    {
        $allrows[] = $row;
    }

    if (count($allrows))
    {
        display_message("info", "Most Recent Comments", "in Reverse Order");
        commenttable($allrows);
    }

    site_footer();
    die;
}
elseif ($action == "edit")
{
    $commentid = 0 + $_GET["cid"];

    if (!is_valid_id($commentid))
    {
        error_message("error", "Error", "Invalid ID.");
    }

    $res = sql_query("SELECT c.*, t.name
                        FROM comments AS c
                        LEFT JOIN torrents AS t ON c.torrent = t.id
                        WHERE c.id = $commentid") or sqlerr(__FILE__, __LINE__);

    $arr = mysql_fetch_assoc($res);

    if (!$arr)
    {
        error_message("error", "Error", "Invalid ID.");
    }

    if ($arr["user"] != $CURUSER["id"] && get_user_class() < UC_MODERATOR)
    {
        error_message("warn", "Warning", "Permission Denied.");
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        $text     = $_POST["text"];
        $returnto = htmlspecialchars($_POST["returnto"]);

        if ($text == "")
        {
            error_message("warn", "Warning", "Comment body cannot be empty!");
        }

        $text = sqlesc($text);

        $editedat = sqlesc(get_date_time());

        sql_query("UPDATE comments
                    SET text = $text, editedat = $editedat, editedby = $CURUSER[id]
                    WHERE id = $commentid") or sqlerr(__FILE__, __LINE__);

        if ($returnto)
        {
            header("Location: $returnto");
        }
        else
        {
            header("Location: $site_url/");
        } //-- Change Later --//
        die;
    }

    if ($CURUSER['torrcompos'] == 'no')
    {
        error_message_center("error", "Error", "Your Torrent Comment Privilage Has Been Removed.");
    }

    site_header("Edit Comment to '".htmlspecialchars($arr["name"])."'");

    print("<h1>Edit Comment to '".htmlspecialchars($arr["name"])."'</h1>\n");
    print("<form method='post' action='comment.php?action=edit&amp;cid=$commentid'>\n");
    print("<input type='hidden' name='returnto' value='{$_SERVER["HTTP_REFERER"]}' />\n");
    print("<input type='hidden' name='cid' value='$commentid' />\n");
    print("<p align='center'><textarea name='text' rows='10' cols='60'>".htmlspecialchars($arr["text"])."</textarea></p>\n");
    print("<p align='center'><input type='submit' class='btn' value='Do it!' /></p>\n");
    print("</form>\n");

    site_footer();
    die;
}
elseif ($action == "delete")
{
    if (get_user_class() < UC_MODERATOR)
    {
        error_message("warn", "Warning", "Permission Denied.");
    }

    $commentid = 0 + $_GET["cid"];

    if (!is_valid_id($commentid))
    {
        error_message("error", "Error", "Invalid ID.");
    }

    $sure = isset($_GET["sure"]) ? (int) $_GET["sure"] : false;

    if (!$sure)
    {
        $referer = $_SERVER["HTTP_REFERER"];
        error_message("warn", "Delete Comment", ""."<a href='comment.php?action=delete&amp;cid=$commentid&amp;sure=1".($referer ? "&amp;returnto=".urlencode($referer) : "")."'>You are about to Delete a Comment. Click Here to Confirm!</a>");
    }

    $res = sql_query("SELECT torrent
                        FROM comments
                        WHERE id = $commentid") or sqlerr(__FILE__, __LINE__);

    $arr = mysql_fetch_assoc($res);

    if ($arr)
    {
        $torrentid = $arr["torrent"];
    }

    sql_query("DELETE
                FROM comments
                WHERE id = $commentid") or sqlerr(__FILE__, __LINE__);

    if ($torrentid && mysql_affected_rows() > 0)

    {
        sql_query("UPDATE torrents
                    SET comments = comments - 1
                    WHERE id = $torrentid");
    }

    $returnto = $_GET["returnto"];

    if ($returnto)
    {
        header("Location: $returnto");
    }
    else
    {
        header("Location: $site_url/");
    } //-- Change Later
    die;
}
elseif ($action == "vieworiginal")
{
    if (get_user_class() < UC_MODERATOR)
    {
        error_message("warn", "Warning", "Permission Denied.");
    }

    $commentid = 0 + $_GET["cid"];

    if (!is_valid_id($commentid))
    {
        error_message("error", "Error", "Invalid ID.");
    }

    $res = sql_query("SELECT c.*, t.name
                        FROM comments AS c
                        LEFT JOIN torrents AS t ON c.torrent = t.id
                        WHERE c.id = $commentid") or sqlerr(__FILE__, __LINE__);

    $arr = mysql_fetch_assoc($res);

    if (!$arr)
    {
        error_message("error", "Error", "Invalid ID $commentid.");
    }

    site_header("Original Comment");

    print("<h1>Original contents of comment #$commentid</h1>\n");
    print("<table border='1' width='100%' cellspacing='0' cellpadding='5'>");
    print("<tr>");
    print("<td class='comment'>\n");
    print htmlspecialchars($arr["ori_text"]);
    print("</td>");
    print("</tr>");
    print("</table><br />");

    $returnto = htmlspecialchars($_SERVER["HTTP_REFERER"]);

    if ($returnto)
    {
        display_message("info", " ", "<a href='$returnto'>Back</a>");
    }

    site_footer();
    die;
}
else
{
    error_message("error", "Error", "Unknown Action");
}

die;

?>