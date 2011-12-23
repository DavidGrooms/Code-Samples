<?php 
ob_start();
include("includes/top.php");
include("common/security.php"); 
include("common/privileges.php");
include("common/upload.php");
include("../common/thumbnail.php");

if(isset($edit_id) && !empty($edit_id))
{
	$edit_id	=	base64_decode($edit_id);
	$pointchk	=	"SELECT * from ads WHERE ads_id = '".$edit_id."'";
	$rs	=	$db->get_row($pointchk,ARRAY_A);
}

if(isset($AddButton) && !empty($AddButton) && $AddButton=='ADD')
{

	if ($_FILES['imagefile']['tmp_name'] != "" )
	{
		$folderpath = "../marketing";
		$ext= array ('gif','jpg','jpeg','GIF','JPG','JPEG');
		$upload = new upload('imagefile', $folderpath.'/', '777', $ext);

		if ( $upload->message =="SUCCESS_FILE_SAVED_SUCCESSFULLY" )
		{
			$uploadfilename = $upload->filename;	
			$targetpath = $folderpath."/";	
			$soruce = $targetpath.$uploadfilename;
			$dest = $targetpath."thumb".$uploadfilename;
			$thumbsave = createThumbnail($soruce, $dest, THUMBNAIL_WIDTH);
			$uploadfilename = addslashes($uploadfilename);
			$thumbsave = addslashes($thumbsave);
			
		}else
		{
			$msg= base64_encode("File is too large or invalid file formate selected.");
			header("location:add_ads.php?msg=$msg&case=3");
			exit;
		}
	}
	
	if ($_FILES['flyerfile']['tmp_name'] != "" )
	{
		$folderpath = "../flyer";
		$ext= array ('jpg','jpeg','JPG','JPEG');
		$upload = new upload('flyerfile', $folderpath.'/', '777', $ext);

		if ( $upload->message =="SUCCESS_FILE_SAVED_SUCCESSFULLY" )
		{
			$uploadflyer = $upload->filename;	
			$targetpath = $folderpath."/";	
			$soruce = $targetpath.$uploadflyer;
			$dest = $targetpath."thumb".$uploadflyer;
			$thumbflyer = createThumbnail($soruce, $dest, THUMBNAIL_WIDTH);
			$uploadflyer = addslashes($uploadflyer);
			$thumbflyer = addslashes($thumbflyer);
			list($width, $height) = @getimagesize($soruce);
			$type = $_FILES['flyerfile']['type'];
			if($type == 'image/jpeg') {
				$ext = 'jpeg';
			}
			elseif($type == 'image/png') {
				$ext = 'png';
			}
			elseif($type == 'image/gif') {
				$ext = 'gif';
			}
			
			$newname = date('ymdhms').'.'.$ext;	
			$destn = $targetpath.$newname;
			$soruce = $targetpath.$uploadflyer;
			rename($soruce,$destn);
			$add = $destn;
			list($widthImg, $heightImg) = getimagesize($add);			
			
				if($type == 'image/jpeg') {			
							$border=0; // Change the value to adjust width
							$im=ImageCreateFromJpeg($add);
							$width=ImageSx($im);
							$height=ImageSy($im);
							$img_adj_width=$width+(2*$border);
							$img_adj_height=$height+90;
							$newimage=imagecreatetruecolor($width,$img_adj_height);
							$border_color = imagecolorallocate($newimage, 255, 255, 255);
							imagefilledrectangle($newimage,0,0,$width,$img_adj_height,$border_color);
							imageCopyResized($newimage,$im,$border,0,0,0,$width,$height,$width,$height);
							ImageJpeg($newimage,$add,100); // change here to $add2 if a new image is to be created
							chmod("$add",0666); // change here to $add2 if a new image is to be created
							list($widthNew, $heightNew) = getimagesize($add);
							if($widthNew>700) {
									echo $msg= base64_encode("Flyer File width is greater than 700px.");
									header("location:add_ads.php?msg=$msg&case=3");
									exit;
							}
				}	

		}else
		{
			echo $msg= base64_encode("Flyer File is too large or invalid file formate selected.");
			header("location:add_ads.php?msg=$msg&case=3");
			exit;
		}
	}
 	 $sqlquery	=	"insert into ads (product_name, website, image, detail, cat_id, company_name, address1, address2, city, state, zip, country, contact_name, 	email, phone, status, billing_option, billing_cycle,imagethumb, add_date, expiry_date,width ,	height 	,file_name 	,file_type 	,cn_size 	,cn_color 	,smal_size 	,smal_color 	,flyer_status 	,flyerthumb) 
                                   values('$product_name', '$website', '$uploadfilename', '$detail', '$cat_id', '$company_name', '$address1', '$address2', '$city', '$state', '$zip', 'USA', '$contact_name', 	'$email', '$phone', '1', '$billing_option', '$billing_cycle','$thumbsave','$startdate','$enddate','$widthNew', '$heightNew', '$newname', '$type', '$cn_size'	,'$cn_color' ,'$smal_size',	'$smal_color',	'$flyer',	'$thumbflyer')";
		$db->query($sqlquery);

     header("Location:ads_list.php?msg=$insert_ok_msg&case=1");
}

if(isset($update_id) && !empty($update_id))
{

	$edit_id = base64_encode($update_id);
	if ($_FILES['imagefile']['tmp_name'] != "" )
	{
		$folderpath = "../marketing";
		$ext= array ('gif','jpg','jpeg','GIF','JPG','JPEG');
		$upload = new upload('imagefile', $folderpath.'/', '777', $ext);

		if ( $upload->message =="SUCCESS_FILE_SAVED_SUCCESSFULLY" )
		{
			$uploadfilename = $upload->filename;	
			if($uploadfilename!="") {
				
				$targetpath = $folderpath."/";	
				$soruce = $targetpath.$uploadfilename;
				$dest = $targetpath."thumb".$uploadfilename;
				$thumbsave = createThumbnail($soruce, $dest, THUMBNAIL_WIDTH);
				$uploadfilename = addslashes($uploadfilename);
				$thumbsave = addslashes($thumbsave);
				$imagevalue = " , image = '".$uploadfilename."'";
				$imagethumb_value = " , imagethumb = '".$thumbsave."'";
				$sqlqry="select  image as dbimage, imagethumb  from ads   where ads_id='$update_id'";
				if($valcmp = $db->get_row($sqlqry))
				{
					$dbimage = stripslashes($valcmp->dbimage);						
					$dbimagethumb = stripslashes($valcmp->imagethumb);						
				}				
			}
			
		}else
		{
			$msg= base64_encode("File is too large or invalid file formate selected.");
			header("location:add_ads.php?msg=$msg&case=3&edit_id=".$edit_id);
			exit;
		}
	}

if ($_FILES['flyerfile']['tmp_name'] != "" )
	{
				$folderpath = "../flyer";
		$ext= array ('jpg','jpeg','JPG','JPEG');
		$upload = new upload('flyerfile', $folderpath.'/', '777', $ext);
			//echo $upload->messag;
		if ( $upload->message =="SUCCESS_FILE_SAVED_SUCCESSFULLY" )
		{
			$uploadflyer = $upload->filename;	
			$targetpath = $folderpath."/";	
			$soruce = $targetpath.$uploadflyer;
			$dest = $targetpath."thumb".$uploadflyer;
			$thumbflyer = createThumbnail($soruce, $dest, THUMBNAIL_WIDTH);
			$uploadflyer = addslashes($uploadflyer);
			$thumbflyer = addslashes($thumbflyer);
			list($width, $height) = @getimagesize($soruce);
			$type = $_FILES['flyerfile']['type'];
			if($type == 'image/jpeg') {
				$ext = 'jpeg';
			}
			elseif($type == 'image/png') {
				$ext = 'png';
			}
			elseif($type == 'image/gif') {
				$ext = 'gif';
			}
			$newname = date('ymdhms').'.'.$ext;	
			$destn = $targetpath.$newname;
			$soruce = $targetpath.$uploadflyer;
			rename($soruce,$destn);

			$add = $destn;
			list($widthImg, $heightImg) = getimagesize($add);			
			
				if($type == 'image/jpeg') {			
							$border=0; // Change the value to adjust width
							$im=ImageCreateFromJpeg($add);
							$width=ImageSx($im);
							$height=ImageSy($im);
							$img_adj_width=$width+(2*$border);
							$img_adj_height=$height+90;
							$newimage=imagecreatetruecolor($width,$img_adj_height);
							$border_color = imagecolorallocate($newimage, 255, 255, 255);
							imagefilledrectangle($newimage,0,0,$width,$img_adj_height,$border_color);
							imageCopyResized($newimage,$im,$border,0,0,0,$width,$height,$width,$height);
							ImageJpeg($newimage,$add,100); // change here to $add2 if a new image is to be created
							chmod("$add",0666); // change here to $add2 if a new image is to be created
							list($widthNew, $heightNew) = getimagesize($add);
							if($widthNew>700) {
						
									echo $msg= base64_encode("Flyer File width is greater than 700px.");
									header("location:add_ads.php?msg=$msg&case=3&edit_id=".$edit_id);
									exit;

							}

				}				
			
			
			$update = " ,width = '$widthNew', height = '$heightNew' , file_name = '$newname' , file_type = '$type' , flyerthumb  = '$thumbflyer'"; 
		}else
		{
			$msg= base64_encode("File is too large or invalid file formate selected.");
			header("location:add_ads.php?msg=$msg&case=3&edit_id=".$edit_id);
			exit;
		}
	}
	
	$sqlquery	=	"update ads set product_name = '$product_name', website = '$website', 
	detail = '$detail', cat_id =  '$cat_id',
	company_name =  '$company_name', address1 =  '$address1', address2 =  '$address2',
	city =  '$city', state = '$state', zip =  '$zip', 
	country = 'USA', contact_name = '$contact_name',
	email = '$email', phone = '$phone', billing_option = '$billing_option', 
	billing_cycle = '$billing_cycle' $imagevalue $imagethumb_value,
	add_date = '$startdate',
	expiry_date = '$enddate',
	cn_size = '$cn_size',
	cn_color  = '$cn_color',
	smal_size = '$smal_size',
	smal_color = '$smal_color',
	flyer_status = '$flyer'
	$update
	
	 where ads_id='$update_id'";
	$db->query($sqlquery);
	
	
	
	header("Location:ads_list.php?msg=$update_ok_msg&case=1");
	exit;
}

?>
<html>
<?php include("common/header.php");?>
<script type="text/javascript" src="js/jquery-1.2.6.min.js"></script>
<script type="text/javascript" src="js/colorpicker/colorpicker.js"></script>
<script type="text/javascript" src="js/colorpicker/eye.js"></script>
<script type="text/javascript" src="js/colorpicker/utils.js"></script>
<script type="text/javascript" src="js/colorpicker/layout.js?ver=1.0.2"></script>
<link href="styles/colorpicker.css" type="text/css" rel="stylesheet" />

<body>

	<div id="statusdiv" name="statusdiv" class="statusOff">
		<img src="images/load.gif"> <b><div id="statustext" style="display: inline;"> </div></b>
	</div>

<table style="border-collapse: collapse;" border="0" cellpadding="0" width="100%" height="100%">
	<tbody> <?php include("common/header_theme.php"); ?>
	<tr>
		<td height="20">&nbsp;</td>
	</tr>
	<tr>
		<td valign="top">
		
<table border="0" width="100%"><tbody><tr>
<td width="10">&nbsp;</td>
<td>
<!-- End page header -->

<!-- Start home -->
	<div class="BodyContainer">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		  <tbody><tr>
	
			<td class="heading1">Ads Managemet</td>
		  </tr>
		<tr>
				<td class="breadcrumb"> 
						<a href="index.php" class="breadcrumb">Home</a> > <a href="ads_list.php" class="breadcrumb">Ads Listings</a></td>
				</tr>	
		  <tr>
			<td class="body">
				<form name="add_frm" id="add_frm" action="" method="post" onSubmit="return chk_addAds_frm();" enctype="multipart/form-data"> 	
					<table id="Table1" border="0" cellpadding="0" cellspacing="0" width="100%">
					  <tbody>
						<tr>
						  <td>&nbsp;</td>
	  				    </tr>
						<tr>
						  <td>
							  <table class="Panel">
								<tbody>
								<tr>
									<td class="Heading2" colspan="3">ADD / EDIT Ads </td>
								  </tr>
								  
								  <?php if($_GET['msg']!="") { ?>
								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">&nbsp;</td>
								    <td colspan="2" align="left" valign="top"><span class="asterik"><?php echo base64_decode($_GET['msg']);?></span></td>
								  </tr>
								 <?php } ?>	
								 

								  <?php if(stripslashes($rs['imagethumb'])!="") { ?>
								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">&nbsp;</td>
								    <td colspan="2" align="left" valign="top"><img src="../marketing/<?php echo stripslashes($rs['imagethumb']);?>"></td>
								  </tr>
								 <?php } ?>	
								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Product Name : </td>
								    <td colspan="2" align="left" valign="top"><input name="product_name" type="text" class="Field300" id="product_name" value="<?php echo stripslashes($rs['product_name']);?>" size="10"><span class="asterik">*</span></td>
								  </tr>

								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Link URL : </td>
								    <td colspan="2" align="left" valign="top"><input name="website" type="text" class="Field300" id="website" value="<?php echo stripslashes($rs['website']);?>" size="10"></td>
								  </tr>
								  
								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Image File : </td>
								    <td colspan="2" align="left" valign="top"><input name="imagefile" type="file" class="Field300" id="imagefile" value=""><br><span class="asterik">(.jpg & .gif only)</span></td>
								  </tr>	

								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Set Qflyer</td>
								    <td colspan="2" align="left" valign="top">
										<?php if($rs['flyer_status']=="0") {
											
											$flyerdiv = "display:none;";
											
										 } elseif($rs['flyer_status']=="1") {

											$flyerdiv = "display:;";
																					 
										 }else {
										 
											$flyerdiv = "display:none;";										 
										 
										 } 
										 ?>
										<select name="flyer" id="flyer" onChange="flyershow()">
											<option value="0" <?php if($rs['flyer_status']=="0") {?> selected="selected" <?php }?>>No</option>
											<option value="1" <?php if($rs['flyer_status']=="1") {?> selected="selected" <?php }?>>Yes</option>
										</select>
									</td>
								    </tr>
									
								  <tr style="<?php echo $flyerdiv;?>" id="flyerpanel">

								    <td colspan="3" align="left" valign="top">
										<table width="100%" cellpadding="0" cellspacing="0" style="border: solid 1px #CCCCCC; padding-top:10px; padding-bottom:10px;">
											<tr>
											<td width="317" align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Company Name Size</td>
											<td  colspan="2" align="left" valign="top">
												<select name="cn_size" id="cn_size">
												<?php for($i=10;$i<=30;$i=$i+2) {?>
													<option <?php if($rs['cn_size'] == $i) echo 'selected="selected"'; ?> value="<?php echo $i;?>"><?php echo $i;?>px</option>
												<?php 
												}
												?>
												</select>											</td>
											<td width="152" rowspan="5" align="left" valign="top" ><?php if($rs['flyerthumb']!=""){ ?> <img align="left" src="../flyer/<?php echo $rs['flyerthumb'];?>" border="0"> <?php } ?></td>
											</tr>
											
											<tr>
												<td width="317" align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Company Name Color</td>
												<td  colspan="2" align="left" valign="top"><?php 
												if(stripslashes($rs['smal_color'])!="") {
												 $cncolor=stripslashes($rs['cn_color']); 
												 } 
												 else 
												 { 
												 $cncolor = "000000"; 
												 }?>
													<input type="text" name="cn_color" id="colorpickerField2" class="dropdown_box fields" value="<?php echo $cncolor;?>" style="width:100px;" />												</td>
										      </tr>
											<tr>
												<td width="317" align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Normal Text Size</td>
												<td  colspan="2" align="left" valign="top">
													 <select name="smal_size" id="smal_size">
														<?php for($i=10;$i<=30;$i=$i+2) {?>
															<option <?php if($rs['smal_size'] == $i) echo 'selected="selected"'; ?> value="<?php echo $i;?>"><?php echo $i;?>px</option>
														<?php } ?>
													</select>												</td>
										      </tr>
											<tr>
												<td width="317" align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Normal Text Color</td>
												<td  colspan="2" align="left" valign="top"><?php if(stripslashes($rs['smal_color'])!=""){$smalcolor=stripslashes($rs['smal_color']);} else {$smalcolor = "000000";}?>
													<input type="text" name="smal_color" id="colorpickerField1" class="fields" value="<?php echo $smalcolor;?>" style="width:100px;" />												</td>
										      </tr>
											<tr>
												<td width="317" align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Upload file:</td>
												<td  colspan="2" align="left" valign="top">
													<input type="file" name="flyerfile" id="flyerfile" /><br>
													<span class="asterik">(.jpg only max 700px width)</span>												</td>
										      </tr>
										</table>
									</td>
								    </tr>	
								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Advertisement Text : </td>
								    <td colspan="2" align="left" valign="top"><textarea name="detail"  class="Field300" style="width:400px; height:200px;" id="detail"><?php echo stripslashes($rs['detail']);?></textarea><span class="asterik">*</span></td>
								  </tr>									  							  

								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Category : </td>
								    <td colspan="2" align="left" valign="top">
									<select name="cat_id" class="Field300" id="cat_id">
									<?php
									$sqlreqqry="SELECT cat_id,	cat_title from mkb_categories order by cat_title asc";	
									if($reqkeys = $db->get_results($sqlreqqry))
									{
										foreach ( $reqkeys as $reqkey)
										{	
										
											$cat_id = stripslashes($reqkey->cat_id); 
											$cat_title = stripslashes($reqkey->cat_title); 	
									?>
											<option value="<?php echo $cat_id;?>" <?php if($cat_id==stripslashes($rs['cat_id'])){?> selected="selected" <?php }?>><?php echo $cat_title;?></option>
									<?php }
									
									}
									?>
									</select>									
									</td>
								  </tr>									  							  


								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Company Name : </td>
								    <td colspan="2" align="left" valign="top"><input name="company_name" type="text" class="Field300" id="company_name" value="<?php echo stripslashes($rs['company_name']);?>" size="10"><span class="asterik">*</span></td>
								  </tr>									  							  

								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Address 1 : </td>
								    <td colspan="2" align="left" valign="top"><input name="address1" type="text" class="Field300" id="address1" value="<?php echo stripslashes($rs['address1']);?>" size="10"><span class="asterik">*</span></td>
								  </tr>									  							  

								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Address 2 : </td>
								    <td colspan="2" align="left" valign="top"><input name="address2" type="text" class="Field300" id="address2" value="<?php echo stripslashes($rs['address2']);?>" size="10"></td>
								  </tr>									  							  

								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">City : </td>
								    <td colspan="2" align="left" valign="top"><input name="city" type="text" class="Field300" id="city" value="<?php echo stripslashes($rs['city']);?>" size="10"><span class="asterik">*</span></td>
								  </tr>									  							  

								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">State : </td>
								    <td colspan="2" align="left" valign="top"><input name="state" type="text" class="Field300" id="state" value="<?php echo stripslashes($rs['state']);?>" size="10"><span class="asterik">*</span></td>
								  </tr>									  							  

								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Zip : </td>
								    <td colspan="2" align="left" valign="top"><input name="zip" type="text" class="Field300" id="zip" value="<?php echo stripslashes($rs['zip']);?>" size="10"><span class="asterik">*</span></td>
								  </tr>									  							  
			
								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Country : </td>
								    <td colspan="2" align="left" valign="top"><b>USA Only</b></td>
								  </tr>									  							  

								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Contact Name : </td>
								    <td colspan="2" align="left" valign="top"><input name="contact_name" type="text" class="Field300" id="contact_name" value="<?php echo stripslashes($rs['contact_name']);?>" size="10"><span class="asterik">*</span></td>
								  </tr>									  							  

								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Email Address : </td>
								    <td colspan="2" align="left" valign="top"><input name="email" type="text" class="Field300" id="email" value="<?php echo stripslashes($rs['email']);?>" size="10"><span class="asterik">*</span></td>
								  </tr>									  							  

								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Phone Number : </td>
								    <td colspan="2" align="left" valign="top"><input name="phone" type="text" class="Field300" id="phone" value="<?php echo stripslashes($rs['phone']);?>" size="10"><span class="asterik">*</span></td>
								  </tr>									  							  
						
								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Billing Option : </td>
								    <td colspan="2" align="left" valign="top">
									<select name="billing_option" id="billing_option"  class="Field300">
										<option value="0" <?php if($rs['billing_option']=="0") { ?> selected="selected" <?php } ?>>Call For Credit Card</option>
										<option value="1" <?php if($rs['billing_option']=="1") { ?> selected="selected" <?php } ?>>Use Previous Credit Card</option>
									</select>
									</td>
								  </tr>	
								  
								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Start Date (yyyy-mm-dd): </td>
								    <td colspan="2" align="left" valign="top"><input name="startdate" type="text" class="Field300" id="" value="<?php echo stripslashes($rs['add_date']);?>" size="10"><span class="asterik">*</span></td>
								  </tr>	
								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">End Date (yyyy-mm-dd): </td>
								    <td colspan="2" align="left" valign="top"><input name="enddate" type="text" class="Field300" id="enddate" value="<?php echo stripslashes($rs['expiry_date']);?>" size="10"><span class="asterik">*</span></td>
								  </tr>	
								  								  
								  <tr>
								    <td align="left" valign="top" nowrap="nowrap" class="SmallFieldLabel">Billing Cycle : </td>
								    <td colspan="2" align="left" valign="top">
									
									<select name="billing_cycle" id="billing_cycle"  class="Field300">
										<?php
										 $sqlreqqry="SELECT pac_id, pac_price FROM mkb_packages order by pac_id desc";	
										if($reqkeys = $db->get_results($sqlreqqry))
										{
											foreach ( $reqkeys as $reqkey)
											{	
											
												$pac_id = stripslashes($reqkey->pac_id); 
												$pac_price = stripslashes($reqkey->pac_price); 						
										?>
											<option value="<?php echo $pac_id;?>" <?php if($rs['billing_cycle']==$pac_id) { ?> selected="selected" <?php } ?>><?php echo $pac_price;?></option>
										<?php
											}
										}
										?>	
									</select>									</td>
								  </tr>	
								  								  								  
								  <tr>
									<td>&nbsp;</td>
									<td colspan="2">
										<input type="hidden" name="updateid" id="updateid" value="<?php echo $edit_id; ?>">
										<?php
										if(isset($edit_id) && !empty($edit_id))
										{
										?>
											<input type="hidden" name="update_id" id="update_id" value="<?php echo $edit_id; ?>">
											<input name="UpdateButton" id="UpdateButton" value="Update" class="FormButton" type="submit">                        
										<?php
										}
										else
										{
										?>
											<input name="AddButton" id="AddButton" value="ADD" class="FormButton" type="submit">
										<?php
										}
										?>									</td>
								  </tr>
								</tbody>
							  </table>						  </td>
						  </tr>
						<tr>
						  <td>&nbsp;</td>
						  </tr>
						<tr>
						  <td>&nbsp;</td>
						</tr>
					  </tbody>
					  </table>
				</form>
			</td>
		  </tr>
		</tbody></table>
		
</div>

<!-- End home -->

<!-- Start pagefooter -->
</td><td width="10">&nbsp;</td></tr></tbody></table>		</td>
	</tr>
	<tr>
		<td height="20">
		<?php include("common/footer.php");?>
		</td>
	</tr>
</tbody></table>
<!-- End pagefooter -->
</body>

</html>