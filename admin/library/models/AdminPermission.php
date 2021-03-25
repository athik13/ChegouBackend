<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class AdminPermission extends Model {

	protected $primaryKey  = "id";

	//protected $table  = "administrators";

	public $timestamps = false;

	protected $fillable = [
		'permission_name', 'status', 'display_order', 'display_group_id'
	];

	function ScopeActive($q){
		$q->where('status', 'Active');
	}

	function group(){
		return $this->belongsTo(AdminPermissionDisplayGroup::class, 'display_group_id', 'id');
	}

	function roles(){
		return $this->belongsToMany(AdminGroup::class, 'admin_group_permission', 'permission_id', 'group_id');
	}
}