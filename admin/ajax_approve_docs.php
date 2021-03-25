<?php
include_once('../common.php');

if($_REQUEST['docsIds']){
	$docs = implode(',',$_REQUEST['docsIds']);
	$query = "SELECT doc_id,req_date,req_file,
	CASE doc_usertype 
		WHEN 'company' THEN (select vEmail from company where iCompanyId = doc_userid)
		WHEN 'driver' THEN (select vEmail from register_driver where iDriverId = doc_userid)
		WHEN 'car' THEN (select vEmail  from driver_vehicle dv INNER JOIN register_driver rd ON dv.iDriverId = rd.iDriverId where dv.iDriverVehicleId = doc_userid)
		WHEN 'store' THEN (select vEmail from company where iCompanyId =doc_userid)
	END AS doc_email, 
	CASE doc_usertype 
		WHEN 'company' THEN (select vCompany from company where iCompanyId = doc_userid)
		WHEN 'driver' THEN (select CONCAT(vName,' ',vLastName) from register_driver where iDriverId = doc_userid)
		WHEN 'car' THEN (select CONCAT(rd.vName,' ',rd.vLastName)  from driver_vehicle dv INNER JOIN register_driver rd ON dv.iDriverId = rd.iDriverId where dv.iDriverVehicleId = doc_userid)
		WHEN 'store' THEN (select vCompany from company where iCompanyId =doc_userid)
	END AS doc_name    	
	FROM document_list WHERE doc_id IN (".$_REQUEST['docsIds'].")";

	$updateDocs = $obj->MySQLSelect($query);

	$userName = $updateDocs[0]['doc_name'];
	$userEmail = $updateDocs[0]['doc_email'];

	$verifiedDocs = '';
	foreach ($updateDocs as $key => $updateDoc) {

		$docId = $updateDoc['doc_id'];
		$req_date = $updateDoc['req_date'];
		$req_file = $updateDoc['req_file'];

		$uQuery = 'UPDATE document_list SET 
					ex_date = "'.$req_date.'" ,
					doc_file ="'.$req_file.'", 
					req_date ="", 
					req_file =""
					WHERE doc_id = '.$docId;
		$updateQuery = $obj->sql_query($uQuery);

		if($updateQuery){
			$verifiedDocs = '1';
		}	
	}

	if(!empty($verifiedDocs)){
		
		/* Send Email to Driver */  
		$getMaildata = [];
		$getMaildata['name'] = $updateDocs[0]['doc_name'];
		$getMaildata['vEmail'] = $updateDocs[0]['doc_email'];
		$mail = $generalobj->send_email_user('EXPIRED_DOCS_APPROVED_NOTIFICATION',$getMaildata);
		
		echo 'Documents Updated Successfully.';
	}else{
		echo 'Something went wrong.';
	}

}

?>