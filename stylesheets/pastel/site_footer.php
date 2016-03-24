<?php global $querytime, $CURUSER, $qtme, $queries, $query_stat, $site_name, $image_dir;

$queries = (!empty($queries) ? $queries : 0);

$qtme['debug']         = array(1); //==Add ids
$qtme['seconds']       = (microtime(true) - $qtme['start']);
$qtme['phptime']       = $qtme['seconds'] - $qtme['querytime'];
$qtme['percentphp']    = number_format(($qtme['phptime'] / $qtme['seconds']) * 100, 2);
$qtme['percentsql']    = number_format(($qtme['querytime'] / $qtme['seconds']) * 100, 2);
$qtme['howmany']       = ($queries != 1 ? 's ' : ' ');
$qtme['serverkillers'] = $queries > 6 ? '<br />'.($queries / 2).' Server Killers ran to show you this Page :) ! =[' : '=]';

if (get_user_class() >= UC_MANAGER)
{
    print("<br />
            <div class='roundedCorners' style='text-align:center;width:80%;border:1px solid black;padding:5px;'>
                <div style='text-align:left;background:transparent;height:25px;'>
                    <span style='font-weight:bold;font-size:12pt;'>Query Stats</span>
                </div>The ".$site_name." Server Killers generated this page in ".(round($qtme['seconds'], 4))." seconds and then took a nap.<br />They had to raid the server ".$queries." time'".$qtme['howmany']."using&nbsp;:&nbsp;
                <span style='font-weight:bold;'>".$qtme['percentphp']."</span>&nbsp;&#37;&nbsp;php&nbsp;&#38;&nbsp;
                <span style='font-weight:bold;'>".$qtme['percentsql']."</span>&nbsp;&#37;&nbsp;sql ".$qtme['serverkillers'].".<br /><br />
            </div>");

    //if (SQL_DEBUG && in_array($CURUSER['id'], $qtme['debug'])) {
    if ($qtme['query_stat'])
    {
        print("<br />
        <div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
        <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>Querys</span></div>
        <table border='0' align='center' width='100%' cellspacing='5' cellpadding='5'>
        <tr>
        <td class='colhead' align='center' width='5%'>ID</td>
        <td class='colhead' align='center' width='10%'>Query Time</td>
        <td class='colhead' align='left' width='85%'>Query String</td>
        </tr>");

        foreach ($qtme['query_stat']
                 AS
                 $key
            =>
                 $value)
        {
            print("<tr>
            <td align='center'>".($key + 1)."</td>
                    <td align='center'>
                        <span style='font-weight:bold;'>".($value['seconds'] > 0.01 ? "
                            <span style='color : #ff0000;' title='You should optimize this query.'>".$value['seconds']."</span>" : "<span style='color : green;' title='Query good.'>".$value['seconds']."</span>")."
                        </span>
                    </td>
            <td align='left'>".htmlspecialchars($value['query'])."<br /></td>
            </tr>");
        }
        print("</table></div><br />");
    }
}
//-- Query Stats --//

?>

<!-- If You Want Support Do Not Remove/Alter These Lines -->
<?php copyright(); ?>
<!-- End Of Credits -->

</td></tr></table>
</body></html>