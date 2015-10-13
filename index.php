<!DOCTYPE html>
<html>
  <head>
    <link rel='stylesheet' href='style.css' type='text/css' media='all' />
  </head>
<body>
<audio id="hiddenbeep" src="sounds/beep.wav" preload="auto"></audio>
<script>

//SOUND BEEP
var beep = document.getElementById("hiddenbeep");
beep.play();
//END SOUND BEEP

//CLOCK SECONDs
var currenttime = '<?php print date("F d, Y H:i:s", time())?>' //PHP method of getting server date

var serverdate=new Date(currenttime)

function padlength(what){
var output=(what.toString().length==1)? "0"+what : what
return output
}

function displaytime(){
serverdate.setSeconds(serverdate.getSeconds()+1)
var timestring=padlength(serverdate.getHours())+""+padlength(serverdate.getMinutes())+""+padlength(serverdate.getSeconds())
document.getElementById("servertime").innerHTML=timestring
}

window.onload=function(){
setInterval("displaytime()", 1000)
}
//END CLOCK SECONDs

//SHOW/HIDE 
function toggle_visibility(id) {
   var e = document.getElementById(id);
   if(e.style.display == 'block')
      e.style.display = 'none';
   else
      e.style.display = 'block';
}
//END SHOW/HIDE

</script>
<?PHP
//OPEN WEATHER.GOV ATOM FEED
  include "class.myatomparser.php";
  $url = "http://alerts.weather.gov/cap/wwaatmget.php?x=MIC005&y=1";
  $atom_parser = new myAtomParser($url);
  $output = $atom_parser->getOutput();	# returns string containing HTML
//END WEATHER.GOV ATOM FEED

//BUTTON SPACER
$s = '<span class="blank">&nbsp;</span>';
//END BUTTON SPACER
?>
<!-- BEGIN DISPLAY OUTPUT -->
  
  <div class="mainwrap">
    <div class="titlewrap">
      Library Computer Access and Retrieval System 
    </div>
  </div>
   
  <div class="box-title" onclick="toggle_visibility('weather');">
    <div class="text">Atmospheric Conditions</div>
    <div class="block">&nbsp;</div>
  </div>  
  <div class="box-100" id="weather" style="display:none;">
    <div class="head"></div>
    <div class="body">
      
      <img src="http://radar.weather.gov/Conus/Loop/NatLoop_Small.gif" style="float:right;">
      
            <?php
	$rss = new DOMDocument();
	$rss->load('http://w1.weather.gov/xml/current_obs/KAZO.rss');
	$feed = array();
	foreach ($rss->getElementsByTagName('item') as $node) {
		$item = array ( 
			'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
			'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
			'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
			'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
			);
		array_push($feed, $item);
	}
	$limit = 5;
	for($x=0;$x<$limit;$x++) {
		$title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
		$link = $feed[$x]['link'];
		$description = $feed[$x]['desc'];
		$date = date('l F d, Y', strtotime($feed[$x]['date']));
                $description = preg_replace("/<img[^>]+\>/i", "", $description); 
		echo '<strong><a href="'.$link.'" title="'.$title.'">'.$title.'</a></strong>';
                echo ''.$description.'';
	}

      if (strpos($output,'There are no active watches') !== true ){
        echo $output;
      }
      ?>
    
    </div>
    <div class="foot"></div>
  </div>
  
  <div class="box-title" onclick="toggle_visibility('security');">
    <div class="text">Security Station</div>
    <div class="block">&nbsp;</div>
  </div>  
  <div class="box-100" id="security" style="display:none;">
    <div class="head"></div>
    <div class="body">
      <img src="http://192.168.10.112:1024/shot.jpg" style="float:left;">
      <?php
	$rss = new DOMDocument();
	$rss->load('http://news.google.com/news?pz=1&cf=all&ned=us&hl=en&topic=w&output=rss');
	$feed = array();
	foreach ($rss->getElementsByTagName('item') as $node) {
		$item = array ( 
			'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
			'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
			'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
			'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
			);
		array_push($feed, $item);
	}
	$limit = 5;
	for($x=0;$x<$limit;$x++) {
		$title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
		$link = $feed[$x]['link'];
		$description = $feed[$x]['desc'];
		$date = date('l F d, Y', strtotime($feed[$x]['date']));
		echo '<img class="list" src="images/bullet.png"><div><a href="'.$link.'" title="'.$title.'" target="_blank">'.$title.'</a></div>';
	}
    ?>    
    </div>
    <div class="foot"></div>
  </div>
  
    <div class="box-title" onclick="toggle_visibility('personel');">
    <div class="text">PERSONNEL Status</div>
    <div class="block">&nbsp;</div>
  </div>  
  <div class="box-100" id="personel" style="display:none;">
    <div class="head"></div>
    <div class="body">
      <?php
	$rss = new DOMDocument();
	$rss->load('http://www.missingkids.com/missingkids/servlet/XmlServlet?act=rss&LanguageCountry=en_US&orgPrefix=NCMC');
	$feed = array();
	foreach ($rss->getElementsByTagName('item') as $node) {
		$item = array ( 
			'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
			'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
			'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
			'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
			);		array_push($feed, $item);
	}
	$limit = 8;
	for($x=0;$x<$limit;$x++) {
		$title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
		$link = $feed[$x]['link'];
		$description = $feed[$x]['desc'];
		$date = date('l F d, Y', strtotime($feed[$x]['date']));
		echo '<img class="list" src="images/bullet.png"><a href="'.$link.'" title="'.$title.'" target="_blank">'.$title.'</a><br />';
	}
    ?>    
    </div>
    <div class="foot"></div>
  </div>
  
      <div class="box-title" onclick="toggle_visibility('replicator');">
    <div class="text">Replicator Menu</div>
    <div class="block">&nbsp;</div>
  </div>  
  <div class="box-100" id="replicator" style="display:none;">
    <div class="head"></div>
    <div class="body">
      <?php
	$rss = new DOMDocument();
	$rss->load('http://rss.allrecipes.com/2/3.xml');
	$feed = array();
	foreach ($rss->getElementsByTagName('item') as $node) {
		$item = array ( 
			'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
			'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
			'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
			'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
			);		array_push($feed, $item);
	}
	$limit = 4;
	for($x=0;$x<$limit;$x++) {
		$title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
		$link = $feed[$x]['link'];
		$description = $feed[$x]['desc'];
		$date = date('l F d, Y', strtotime($feed[$x]['date']));
		echo '<img class="list" src="images/bullet.png"><a href="'.$link.'" title="'.$title.'" target="_blank">'.$title.'</a><br />';
	}
    ?>    
    </div>
    <div class="foot"></div>
  </div>
    
  <div class="box-title" onclick="toggle_visibility('engineering');">
    <div class="text">Engineering Systems</div>
    <div class="block">&nbsp;</div>
      <div style="clear:both;"></div>

  </div>
    
  <div class="box-100" id="engineering" style="display:none;">
    <div class="head"></div>
    <div class="body">
<?php 
  function tdr($tdr1,$tdr2) {
    echo "<tr><td>$tdr1</td><td>$tdr2</td></tr>\n";
  }

  if (!exec("cat /etc/slackware-version", $vers_)) {
  exec("cat /etc/*-release", $vers_); }
    exec("cat /proc/meminfo | grep Mem", $meminf_);
    $vers = str_replace(array("\\n","\\l"),"",$vers_);
    $freeSpace = round(disk_free_space("/") / 1073741824, 3);
    $totalSpace = round(disk_total_space("/") / 1073741824, 3);
    $meminf1= explode(":",$meminf_[0]);
    $meminf2= explode(":",$meminf_[1]);
    
    echo"<table>\n";
    tdr("OS:",  $vers[0]);
    tdr("Disk space:",  " $totalSpace GB");
    tdr("Disk Free:",   " $freeSpace GB");
    tdr("Memory",  " $meminf1[1]");
    tdr("Free",  " $meminf2[1]");
    echo"</table>\n";
?>
    </div>
    <div class="foot"></div>
  </div>
  
  <div class="date-n-time">
    <div class="text"><?= date('mdy'); ?>.<span id="servertime"></span> <?= date('O'); ?></div>
    <div class="block">&nbsp;</div>
  </div>
  
  <div class="displaywrap">
    <div class="displaytitlewrap">
      Main system
    </div>
  </div>
  
  <div class="btn">
    <a href="http://www.geekologie.com" target="_blank">Geekologie <?=$s;?> Blog</a>
  </div>
  <div class="btn">
    <a href="http://www.Gizmodo.com" target="_blank">Gizmodo</a>
  </div>
  <div class="btn">
    <a href="https://news.google.com" target="_blank">Google <?=$s;?> News</a>
  </div>
  <div class="btn">
    <a href="http://www.nasa.gov/" target="_blank">NASA</a>
  </div>
  <div class="btn">
    <a href="https://robertsspaceindustries.com/" target="_blank">Roberts <?=$s;?> Space <?=$s;?> Industries</a>
  </div>
  <div class="btn">
    <a href="http://www.weather.gov/" target="_blank">Weather <?=$s;?> Gov</a>
  </div>
  <div class="btn">
    <a href="http://failblog.cheezburger.com/" target="_blank">Fail <?=$s;?> Blog</a>
  </div>
  <div class="btn">
    <a href="http://slickdeals.net/" target="_blank">Slick <?=$s;?> Deals</a>
  </div>
  <div class="btn">
    <a href="http://www.amazon.com/" target="_blank">Shop <?=$s;?> Amazon</a>
  </div>
  <div class="btn">
    <a href="http://www.drudgereport.com/" target="_blank">Drudge <?=$s;?> Report</a>
  </div>
  
<!-- END DISPLAY OUTPUT -->
</body>
</html>

  
  