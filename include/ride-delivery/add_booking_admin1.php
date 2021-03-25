<div class="col-lg-12 add-booking-radiobut">
	<input class="add-booking" id="r1" name="eType" type="radio" value="Ride" <?php if($etype == 'Ride') { echo 'checked'; } ?> onChange="show_type(this.value),showVehicleCountryVise($('#vCountry option:selected').val(),'<?php echo $iVehicleTypeId; ?>',this.value);" checked="checked"><label for="r1">Ride</label>
</div>								
<div class="col-lg-12 add-booking-radiobut">
	<input id="r2" name="eType" type="radio" value="Deliver" <?php if($etype == 'Deliver') { echo 'checked'; } ?> onChange="show_type(this.value),showVehicleCountryVise($('#vCountry option:selected').val(),'<?php echo $iVehicleTypeId; ?>',this.value);"><label for="r2">Delivery</label>
</div> 