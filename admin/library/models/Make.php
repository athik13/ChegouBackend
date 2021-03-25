<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class Make extends Model {

	protected $primaryKey  = "iMakeId";

	protected $table  = "make";

	public $timestamps = false;

	protected $fillable = [
		'vMake', 'eStatus'
	];

	function models(){
		$this->hasMany(Country::class, 'iMakeId', 'iMakeId');
	}
}