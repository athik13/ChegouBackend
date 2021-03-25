<?php
namespace Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel {

	protected $primaryKey  = "iModelId";

	protected $table  = "model";

	public $timestamps = false;

	protected $fillable = [
		'iModelId', 'iMakeId', 'vTitle', 'eStatus'
	];

	function make(){
		$this->hasOne(Country::class, 'iMakeId', 'iMakeId');
	}
}