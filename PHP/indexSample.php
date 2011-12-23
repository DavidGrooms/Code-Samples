<?php
  /* index.php */
  require('includes/header.php');
  require_once('../../php_inc/xxxconnect.php');
  
 
  $dbh = mysql_connect(xxx_DB_HOST, xxx_DB_USER, xxx_DB_PASS);
  mysql_select_db(xxx_DB_NAME, $dbh);

  if( !$dbh )
	 die( 'Could not connect to db' );
  if( !mysql_select_db('hostname_production', $dbh) )
	 die( 'Database selection failed' );
	 
$sql = "select id, subject, company, product_id from commitments where product_id is not null and commitments.subject > 'a' and commitments.company > 'a' ORDER BY commitments.id DESC limit 0,3";
	$q = mysql_query($sql,$dbh);

	$i = 0;
	$page = array();
  	while(  $row = mysql_fetch_array($q, MYSQL_ASSOC) ) 
	{
	  		$sql = "select filename from uploads where comid = " . $row["id"] . " and thumbnail = 'thumb'";
				$q2 = mysql_query($sql,$dbh);
				$rs = mysql_fetch_array($q2, MYSQL_ASSOC);
				
			$i++;
	  		$rowvars = array("image" => "/images/flyers/" . $rs["filename"], "id" => $row["id"],"subject" => $row["subject"],"company" => $row["company"],"comid" => $row["id"]);
			$page[$i] = $rowvars;
  	}
?>

      <div class="banner">
		<p>
        	<a href="about.php"><img src="images/allAmericanMarketing.jpg" alt="All American Marketing" title="All American Marketing" width="350px" height="150px" /></a>
			<div class="adcopy">
					Here's the 'All American' products you've been looking for. 
			</div>
		</p>
      </div>
	  <!--close banner-->
      <div class="bottom">
      		<div class="deals">
              <p>
                <strong>FEATURED PRODUCTS</strong>
              </p>
            <!--close deals-->
            </div>
            <div class="left">
              <div class="ad">
				<a href="http://hostname.somewhere.com/templets/show_ad/1?s=s&t=pt&comid=<?= $page[1]["id"] ?>" target='_blank'><img src="http://hostname.somewhere.com<?= $page[1]["image"] ?>"/></a>
                <br />
                <h3><?=  $page[1]["company"] ?></h3>
                <br />
                <p>
                <?= $page[1]["subject"] ?>
                <br />
                <a href="http://hostname.somewhere.com/templets/show_ad/1?s=s&t=pt&comid=<?= $page[1]["id"] ?>" target='_blank'>More Information</a>
                </p>
              <!--close ad-->
              </div>
              <div class="ad">
                <a href="http://hostname.somewhere.com/templets/show_ad/1?s=s&t=pt&comid=<?= $page[2]["id"] ?>" target='_blank'><img src="http://hostname.somewhere.com<?= $page[2]["image"] ?>"/></a>
                <br />
                <h3><?=  $page[2]["company"] ?></h3>
                <br />
                <p>
                <?= $page[2]["subject"] ?>
                <br />
                <a href="http://hostname.somewhere.com/templets/show_ad/1?s=s&t=pt&comid=<?= $page[2]["id"] ?>" target='_blank'>More Information</a>
                </p>
              <!--close ad-->
              </div>
              <div class="ad">
                <a href="http://hostname.somewhere.com/templets/show_ad/1?s=s&t=pt&comid=<?= $page[3]["id"] ?>" target='_blank'><img src="http://hostname.somewhere.com<?= $page[3]["image"] ?>"/></a>
                <br />
                <h3><?=  $page[3]["company"] ?></h3>
                <br />
                <p>
                <?= $page[3]["subject"] ?>
                <br />
                <a href="http://hostname.somewhere.com/templets/show_ad/1?s=s&t=pt&comid=<?= $page[3]["id"] ?>" target='_blank'>More Information</a>
                </p>
              <!--close ad-->
              </div>
            <!--close left-->
            </div>
            <div class="right">
              <h2>Don't want to miss out on the latest Flyers?</h2>
              <br />
              <p>
              If you would like to stay up to date on all of our
			  somewhere then click the button below to sign up!
			  It is quick and easy and you will be accessing deals in no time at all!
              </p>
              <br />
              <br />
              <a href="http://hostname.somewhere.com/register"><img src="images/register.jpg" alt="Registration" /><div class="signup"></div></a>
              <br />
              <p class="note">
              &quot; If you want more information on all the different ways to 
			  market all American products 
			  <br /> click here ---> <a href="http://www.somewhere.com">Buy American!</a>
			  </p> 
            <!--close right-->
            </div>
      <!--close bottom-->
  	  </div>
<?php
  include('includes/footer.php');
?>