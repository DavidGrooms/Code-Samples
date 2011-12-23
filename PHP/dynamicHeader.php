<?php
  /* header.php */
  
  $path = $_SERVER['PHP_SELF'];
  $page = basename($path);
  $page = basename($path, '.php');
?>

<!DOCTYPE html>

<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <?php
			echo "<title>".(isset($title)?$title:"SomeCompany | Take Advantage of Online Marketing")."</title>\n";
		?>
    <link href="includes/styles.css" rel="stylesheet" />
    <link href="autocomplete.css" rel="stylesheet" />
    <link href="http://www.somecompany.com/favicon.png" rel="icon"/>
	<script src="http://ie7-js.googlecode.com/svn/version/2.0(beta3)/IE8.js" type="text/javascript"></script>
    <script language="JavaScript" src="js/jquery-1.3.2.min.js" ></script>
    <script language="JavaScript" src="js/autocomplete.js" ></script>
	
    <script type="text/javascript">
      function suggestValues() {
        $("#getcat").autocomplete("getCategory.php", {
          width: 260,
          selectFirst: false
        });
      }
      $(document).ready(function(){
        suggestValues();
      });
    </script>
	
	</head>

  <body>
  	<?php include_once("analyticstracking.php") ?>
    <div class="mainBg">
      <h1 class="hide">SomeCompany - Archive</h1>
      <div class="mainWrapper">
        <div class="nav">
          <ul>
            <li><a<? if($page == 'index'): ?> class="active"<? endif ?>  href="/products/"> HOME </a></li>
            <li><a<? if($page == 'today'): ?> class="active"<? endif ?>  href="today.php">LATEST</a></li>
            <li><a<? if($page == 'archive'): ?> class="active"<? endif ?>  href="archive.php">ALL PRODUCTS</a></li>
			<li><a<? if($page == 'flyers'): ?> class="active"<? endif ?>  href="http://www.somecompany.com/flyers">FLYERS</a></li>
            <li><a href="http://backend.somecompany.com/register">SIGN UP</a></li>
            <li><a<? if($page == 'about'): ?> class="active"<? endif ?>  href="http://www.somecompany.com/about.html">ABOUT</a></li>
            <li><a<? if($page == 'contact'): ?> class="active"<? endif ?>  href="http://www.somecompany.com/contact.html">CONTACT</a></li>
          </ul>
          <!--close nav-->
        </div>
        
        <div class="top">
          <a href="http://www.somecompany.com"><img class="logo" src="http://www.somecompany.com/images/sfbanner4.jpg" alt="somecompany" title="somecompany" /></a>
          <form action="archive.php" method="get">
            <input class="txt" type="text" size="45" name="getcat" id="getcat" value="" />
            <input class="search" type="image" width="45" height="26" src="http://www.somecompany.com/images/searchButton.png" alt="search somecompany" title="search somecompany" id="searchBtn" />
          </form>
          <!--close top-->
        </div>
      
<?php
  /* end header.php */
?>