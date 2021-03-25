<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model {

	protected $primaryKey  = "iSettingId";

	protected $table  = "configurations";

	public $timestamps = false;

	protected $fillable = [
		'tDescription', 'vName', 'vValue', 'vOrder', 'eType', 'eStatus', 'tHelp', 'eInputType', 'tSelectVal', 'eAdminDisplay', 'eRequireField'
	];
}