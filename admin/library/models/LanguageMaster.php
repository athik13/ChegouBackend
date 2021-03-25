<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class LanguageMaster extends Model {

	protected $primaryKey  = "iLanguageMasId";

	protected $table  = "language_master";

	public $timestamps = false;

	protected $fillable = [
		'vTitle', 'vTitle_EN', 'vCode', 'vGMapLangCode', 'vLangCode', 'vCurrencyCode', 'vCurrencySymbol', 'iDispOrder', 'eStatus', 'eDefault', 'eDirectionCode'
	];

	function ScopeActive($query){
		return $query->where('eStatus', 'Active');
	}

	function labels(){
		return $this->hasMany(LanguageLabel::class, 'vCode', 'vCode');
	}
}