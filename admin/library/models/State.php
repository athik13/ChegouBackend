<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model {

	protected $primaryKey  = "iStateId";

	protected $table  = "state";

	public $timestamps = false;

	protected $fillable = [
		'iCountryId', 'vStateCode', 'vState', 'eStatus'
	];


	function country(){
		$this->hasOne(Country::class, 'iCountryId', 'iCountryId');
	}

	function cities(){
		$this->hasMany(City::class, 'iStateId', 'iStateId');
	}
}