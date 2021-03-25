<?php
include_once('common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
} 
	$responce=array();		 
	  
	 	 
				 $OrderDetails=$_SESSION['OrderDetails'];
				$count=count($_SESSION['OrderDetails']);
				   unset($_SESSION['OrderDetails']);
				$id=$_REQUEST['id'];
				$removeid=$_REQUEST['removeid'];
				
				$responce['OrderDetails']=array();
			 for($i=0;$i<$count;$i++)
				 {	
							
									$addoptions=array();
								 
						if($i== $removeid)
							{
									$addoptions['iMenuItemId']=$OrderDetails[$i]['iMenuItemId'];
									$addoptions['iFoodMenuId']=$OrderDetails[$i]['iFoodMenuId'];
									$addoptions['vOptionId']=$OrderDetails[$i]['vOptionId'];
									$addoptions['iQty']=$OrderDetails[$i]['iQty'];
									$addoptions['vAddonId']=$OrderDetails[$i]['vAddonId'];
									$addoptions['tInst']=$OrderDetails[$i]['tInst'];
									$addoptions['typeitem']= 'remove';						 
							}
							  else{ 
									$addoptions['iMenuItemId']=$OrderDetails[$i]['iMenuItemId'];
									$addoptions['iFoodMenuId']=$OrderDetails[$i]['iFoodMenuId'];
									$addoptions['vOptionId']=$OrderDetails[$i]['vOptionId'];
									$addoptions['iQty']=$OrderDetails[$i]['iQty'];
									$addoptions['vAddonId']=$OrderDetails[$i]['vAddonId'];
									$addoptions['tInst']=$OrderDetails[$i]['tInst'];
									$addoptions['typeitem']=$OrderDetails[$i]['typeitem'];
							}
									array_push($responce['OrderDetails'],$addoptions); 
						
				 }  
		
					$_SESSION['OrderDetails']=$responce['OrderDetails'];
					$ld=0;
					foreach($responce['OrderDetails'] as $OrderDeta)
					{
							  	$OrderDeta['typeitem'];
								
								if($OrderDeta['typeitem']=='new')
								{
									$ld=1;
								}
					}						
					if($ld==0)
					{   
						unset($_SESSION['OrderDetails']);
					}	
	 	echo json_encode(array('count'=>'1','responce'=>$responce['OrderDetails']));    exit;
				
?>