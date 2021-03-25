<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class AdminGroup extends Model {

	protected $primaryKey  = "iGroupId";

	//protected $table  = "administrators";

	public $timestamps = false;

	protected $fillable = [
		'vGroup', 'eStatus'
	];

	function permissions(){
		return $this->belongsToMany(AdminPermission::class, 'admin_group_permission',  'group_id', 'permission_id');
	}
}