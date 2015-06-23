<?php
echo '<div class="title box">User Info Lookup</div>';
$usrlogged = $_SERVER['PHP_AUTH_USER'];

$settings = parse_ini_file("userinfo.conf", true);

if ($_GET["u"]){
  $user = $_GET["u"];
}
else {$user = $_SERVER['PHP_AUTH_USER'];}

$servername = $settings[kace][kace];
$username = $settings[kace][kaceuser];
$password = $settings[kace][kacepass];
$dbname = $settings[kace][kacedb];

$lserver = $settings[ldap][ldapserver];
$luser = $settings[ldap][ldapuser];
$lpsw = $settings[ldap][ldappass];
$ldn = $settings[ldap][ldapDN];
$lsearch = "samaccountname=$user";

$url = $settings[anixis][anixisURL] . $user;
$opts = array('http' =>
    array(
        'method' => 'GET',
        'max_redirects' => '0',
        'ignore_errors' => '1'
    )
);

$context = stream_context_create($opts);
$stream = fopen($url, 'r', false, $context);
$enrolled = stream_get_contents($stream);
fclose($stream);
if (strpos($enrolled,'true') !== false) {
$anixis = 'User is enrolled in Anixis';
} else {
$anixis = 'User is NOT enrolled in Anixis';
}
$ds=ldap_connect($lserver);

ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);

$r=ldap_bind($ds, $luser, $lpsw);
$sr=ldap_search($ds, $ldn, $lsearch);
$data = ldap_get_entries($ds, $sr);
//echo "Found " . $data["count"] . " LDAP entries for this user.<br>";

for ($i=0; $i<$data["count"]; $i++) {
  echo '<div id="container1"><div id="container">';
  echo '<div class="user box">';
  echo "SamID Found: " .  $data[$i]["samaccountname" ][0] . "<br>";
  echo "Phone number: " . $data[$i]["telephonenumber"][0] . "<br>";
  if ($data[$i]["lockouttime"][0] > 0){
  echo "Locked out: yes<br>";}
  echo "Location: "    .  $data[$i]["physicaldeliveryofficename"][0] . "<br>";
  echo "Title: "       .  $data[$i]["title"][0] . "<br>";
  echo "Department:"   .  $data[$i]["department"][0] . "<br>";
  echo "$anixis"       .  '<br>';
  echo '<span id="txtHint"></span>';
  echo '</div>';

}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }
$sql = "SELECT * FROM MACHINE WHERE USER='". "$user" ."'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
    echo '<div class="computer box">';
    echo 'Network: '  
          . $row["NAME"] . '  <span style="float:right;">'
          . $row["IP"] . '</span>';
    echo ' (' . $row["OS_ARCH"]          . ')<br>';
    echo 'Last Seen: '     . $row["LAST_SYNC"];
	$reboot = substr($row["LAST_REBOOT"], 0, 10);
    echo '<span style="float:right;">Last Rebooted: ' . $reboot . '</span><br>';  
    echo "Model: "         . $row["CS_MODEL"]    . "<br>";
    echo "IE Version: "    . $row["IE_VERSION"]  . "<br>";
	$ipaddr=$row["IP"];
	exec(sprintf('ping -c 1 -W 1 %s', escapeshellarg($ipaddr)), $res, $rval);
	echo '<span style="float:right;">Ping Result: ';
	if ($rval == 1){echo '<font style="color:red;">Host Offline</font>';}
	if ($rval == 0){echo '<font style="color:#00CC00;">Host Online</font>';}
	echo '</span>';
    echo "Memory: "        
          . $row["RAM_USED"] . "/" 
          . $row["RAM_TOTAL"] . "<br>";
    echo '</div>';
  }
} else {
    echo '<div class="computer box">';
    echo "No results.  Please note that you can use asterisks in your query.";
    echo '</div>';
}
  echo '</div>';
mysqli_close($conn);
ldap_close($ds);
?>

<html>
<head>
<link href='http://fonts.googleapis.com/css?family=Abel' rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css" href="styles/one.css">

</head>
<body>
<div class="entry box">
  <form method="submit">
    Enter samID for lookup:<span class="spacer"></span><input id="saminput" label="samid" type="text" name="u"></input>
    <button id="searchbtn">Search</button>
  </form>
</div>
</body>
</html>
