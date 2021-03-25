<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class LocationMaster extends Model {

	protected $primaryKey  = "iLocationId";

	protected $table  = "location_master";

	public $timestamps = false;

	protected $fillable = [
		'iLocationId', 'iCountryId', 'vLocationName', 'tLatitude', 'tLongitude', 'eStatus', 'eFor'
	];

	function admins(){
		return $this->belongsToMany(Administrator::class, 'admin_locations', 'location_id', 'admin_id');
	}

	function ScopeAdminLocations($q){
		$q->where('eFor', 'VehicleType')->active();
	}

	function ScopeActive($q){
		$q->where('eStatus', 'Active');
	}


}