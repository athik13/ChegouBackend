<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model {

	protected $primaryKey  = "iCountryId";

	protected $table  = "country";

	public $timestamps = false;

	protected $fillable = [
		'vCountry', 'vCountryCode', 'vCountryCodeISO_3', 'vPhoneCode', 'vTimeZone', 'vAlterTimeZone', 'vEmergencycode', 'eStatus', 'eUnit', 'fTax1', 'fTax2', 'vCurrency', 'eEnableToll'
	];


	function states(){
		$this->hasMany(State::class, 'iCountryId', 'iCountryId');
	}

	function cities(){
		$this->hasMany(City::class, 'iCountryId', 'iCountryId');
	}
}