<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model {

	protected $primaryKey  = "iCityId";

	protected $table  = "city";

	public $timestamps = false;

	protected $fillable = [
		'vCity', 'iCountryId', 'iStateId', 'eStatus'
	];

	function country(){
		$this->hasOne(Country::class, 'iCountryId', 'iCountryId');
	}

	function state(){
		$this->hasOne(State::class, 'iStateId', 'iStateId');
	}
}