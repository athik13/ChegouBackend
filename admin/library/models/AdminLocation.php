<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class AdminLocation extends Model {

	protected $primaryKey  = "id";

	protected $table  = "admin_locations";

	public $timestamps = false;

	protected $fillable = [
		'admin_id', 'location_id'
	];

	function admins(){
		return $this->hasMany(Administrator::class, 'iAdminId', 'admin_id');
	}
	
	function locations(){
		return $this->hasMany(LocationMaster::class, 'iLocationId', 'location_id')->adminLocations();
	}
}