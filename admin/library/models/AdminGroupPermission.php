<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class AdminGroupPermission extends Model {

	protected $primaryKey  = "id";

	protected $table  = "admin_group_permission";

	public $timestamps = false;

	protected $fillable = [
		'group_id', 'permission_id'
	];


	function role(){
		return $this->belongsTo(AdminGroup::class, 'group_id', 'iGroupId');
	}
	
	function permission(){
		return $this->belongsTo(AdminPermission::class, 'permission_id', 'id');
	}
}