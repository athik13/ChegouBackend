<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class AdminPermissionDisplayGroup extends Model {

	protected $primaryKey  = "id";

	protected $table = "admin_permission_display_groups";

	public $timestamps = false;

	protected $fillable = [
		'name', 'status'
	];


	function permissions(){
		return $this->hasMany(AdminPermission::class, 'display_group_id', 'id');
	}
}