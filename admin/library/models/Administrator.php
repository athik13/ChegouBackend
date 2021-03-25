<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class Administrator extends Model {
    
	protected $primaryKey  = "iAdminId";

	protected $table  = "administrators";

	protected $hidden  = ["vPassword"];

	public $timestamps = false;

	protected $fillable = [
		'iGroupId', 'vFirstName', 'vLastName', 'vEmail', 'vContactNo', 'vCode', 'vPassword', 'vCountry', 'vState', 'vCity', 'vAddress', 'vAddressLat', 'vAddressLong', 'fHotelServiceCharge', 'vPaymentEmail', 'vBankAccountHolderName', 'vAccountNumber', 'vBankName', 'vBankLocation', 'vBIC_SWIFT_Code', 'eStatus', 'eDefault'
	];

	function roles(){
		return $this->belongsTo(AdminGroup::class, 'iGroupId', 'iGroupId');
	}

	function locations(){
		return $this->belongsToMany(LocationMaster::class, 'admin_locations', 'admin_id', 'location_id');
	}
        
}