$(function () {
    $.validator.addMethod("validate_facebook_url", function (value, element) {
        return this.optional(element) || /^(https?:\/\/)?((w{3}\.)?)facebook\.com\/(#!\/)?[a-z0-9_/+&%.?=]+$/i.test(value);
    });
    $.validator.addMethod("validate_twitter_url", function (value, element) {
        return this.optional(element) || /^(https?:\/\/)?((w{3}\.)?)twitter\.com\/(#!\/)?[a-z0-9_/+&%.?=]+$/i.test(value);
    });
    $.validator.addMethod("validate_googleplus_url", function (value, element) {
        return this.optional(element) || /^(https?:\/\/)?((w{3}\.)?)plus.google\.com\/(#!\/)?[a-z0-9_/+&%.?=]+$/i.test(value);
    });
    $.validator.addMethod("validate_linkedin_url", function (value, element) {
        return this.optional(element) || /^(https?:\/\/)?((w{3}\.)?)linkedin\.com\/(#!\/)?[a-z0-9_/+&%.?=]+$/i.test(value);
    });
    $.validator.addMethod("validate_youtube_url", function (value, element) {
        return this.optional(element) || /^(https?:\/\/)?((w{3}\.)?)youtube\.com\/(#!\/)?[a-z0-9_/+&%.?=]+$/i.test(value);
    });
    $.validator.addMethod("validate_pinterest_url", function (value, element) {
        return this.optional(element) || /^(https?:\/\/)?((w{3}\.)?)pinterest\.com\/(#!\/)?[a-z0-9_/+&%.?=]+$/i.test(value);
    });
    $.validator.addMethod("phonevalidate", function (value, element) {
        var value = value.split(" ").join("");
        value = value.replace(/\(|\)|\s+|-/g, '');
        return this.optional(element) || /^(?:[0-9] ?){6,14}[0-9]$/.test(value);
    });
    $.validator.addMethod("validate_prefix_code", function (value, element) {
        //return this.optional(element) || /^\+[1-9]{1,1}|\+[1-9]{1,1}[0-9]{1,1}|\+[0-9]{1,1} [0-9]{1,3}|\+[1-9]{1,1}[0-9]{1,3}$/i.test(value);
        return this.optional(element) || /^\+(([2-9]{1}([0-9]{0,2})$)|([1]{1}?(\s)?([1-9]{1}[0-9]{2})$)|([1-9]{1}$))/i.test(value);
    });
    $.validator.addMethod("validate_name", function (value, element) {
        return this.optional(element) || /^[a-zA-Z\s\(\)\_\-\"\'\,\:\`\\\/\.\{\}\[\]]+$/i.test(value);
    });
    $.validator.addMethod("validate_date", function (value, element) {
        return this.optional(element) || /^\d\d?-\d\d-\d\d\d\d/.test(value);
    });
    $.validator.addMethod("validate_zipcode", function (value, element) {
        var value = value.split(" ").join("");
        return this.optional(element) || /^[0-9a-zA-Z\s{0,1}]{5,6}$/.test(value);
    });
});

$(function () {
	$('#_add_recipient').validate({
		ignore: 'input[type=hidden]',
		errorClass: 'help-block',
		errorElement: 'span',
		errorPlacement: function (error, e) {
			e.parents('.form-group > div').append(error);
		},
		highlight: function (e) {
			$(e).closest('.form-group').removeClass('has-success has-error').addClass('has-error');
			$(e).closest('.help-block').remove();
		},
		success: function (e) {
			e.closest('.form-group').removeClass('has-success has-error');
			e.closest('.help-block').remove();
			e.closest('.help-inline').remove();
		},
		rules: {
			vAddress: {required: true},
			vName: {required: true},
			iCountryId: {required: true},
			vPhone: {required: true, phonevalidate: true},
			vEmail: {required: true, email: true}
		},
		messages: {
			vAddress: "Address is Required.",
			vName: "Name is Required.",
			iCountryId: "Country is Required.",
			vPhone: {
				required: "Phone Number is Required.",
				phonevalidate: "Please enter valid Phone Number."
			},
			vEmail: {
				required: "Email Address is Required.",
				email: "Please enter valid Email Address."
			}
		},
		submitHandler: function(form){
			saveNewRecipient();
		}
	});
	
	
	$('#_add_delivery_location').validate({
		ignore: 'input[type=hidden]',
		errorClass: 'help-block',
		errorElement: 'span',
		errorPlacement: function (error, e) {
			e.parents('.form-group > div').append(error);
		},
		highlight: function (e) {
			$(e).closest('.form-group').removeClass('has-success has-error').addClass('has-error');
			$(e).closest('.help-block').remove();
		},
		success: function (e) {
			e.closest('.form-group').removeClass('has-success has-error');
			e.closest('.help-block').remove();
			e.closest('.help-inline').remove();
		},
		rules: {
			iRecipientId: {required: true},
			vWeight: {required: true},
			vQuantity: {required: true}
		},
		messages: {
			iRecipientId: "Recipient is Required.",
			vWeight: "Weight is Required.",
			vQuantity: "Quantity is Required."
		}
	});
	
	$('#_address_form').validate({
		//ignore: 'input[type=hidden]',
		errorClass: 'help-block',
		errorElement: 'span',
		errorPlacement: function (error, e) {
			e.parents('.form-group > div').append(error);
		},
		highlight: function (e) {
			$(e).closest('.form-group').removeClass('has-success has-error').addClass('has-error');
			$(e).closest('.help-block').remove();
		},
		success: function (e) {
			e.closest('.form-group').removeClass('has-success has-error');
			e.closest('.help-block').remove();
			e.closest('.help-inline').remove();
		},
		rules: {
			fromLocation: {required: true},
			toLocation: {required: function(e){
				return $('input[name=eType]:checked').val() == 'Ride';
			}}
		},
		messages: {
			fromLocation: "Pickup Location is Required.",
			toLocation: "Dropoff Location is Required."
		},
		submitHandler: function(form){
			//saveNewDeliveryLocation();
		}
	});
	
	$('#_Payment_form').validate({
		//ignore: 'input[type=hidden]',
		errorClass: 'help-block',
		errorElement: 'span',
		errorPlacement: function (error, e) {
			e.parents('.form-group > div').append(error);
		},
		highlight: function (e) {
			$(e).closest('.form-group').removeClass('has-success has-error').addClass('has-error');
			$(e).closest('.help-block').remove();
		},
		success: function (e) {
			e.closest('.form-group').removeClass('has-success has-error');
			e.closest('.help-block').remove();
			e.closest('.help-inline').remove();
		},
		rules: {
			vCouponCode: {required: true, remote: {
					url: 'booking_webservice.php',
					data: {type: 'CheckPromoCode'}
			}}
		},
		messages: {
			vCouponCode: {required: "Pickup Location is Required.", remote: "" }
		},
		submitHandler: function(form){
			//saveNewDeliveryLocation();
		}
	});
	
	
	$('#_rider_login').validate({
		ignore: 'input[type=hidden]',
		errorClass: 'help-block',
		errorElement: 'span',
		errorPlacement: function (error, e) {
			e.parents('.form-group > div').append(error);
		},
		highlight: function (e) {
			$(e).closest('.form-group').removeClass('has-success has-error').addClass('has-error');
			$(e).closest('.help-block').remove();
		},
		success: function (e) {
			e.closest('.form-group').removeClass('has-success has-error');
			e.closest('.help-block').remove();
			e.closest('.help-inline').remove();
		},
		rules: {
			'vEmail': {required: true, email: true,remote: {
							url: 'booking-ajax/ajax_validate_email_new.php',
							type: "post"
						}},
			'vPassword': {required: true}
		},
		messages: {
			'vEmail': {
				required: "Email Address is required.",
				email: "Please enter valid Email Address.",
				remote: "This rider is not exists / deleted."
			},
			'vPassword': {
				required: "Password is required."
			}
		},
		submitHandler: function(form){
			userSignIn();
		}
	});
	
	$('#_rider_register').validate({
		ignore: 'input[type=hidden]',
		errorClass: 'help-block',
		errorElement: 'span',
		errorPlacement: function (error, e) {
			e.parents('.form-group > div').append(error);
		},
		highlight: function (e) {
			$(e).closest('.form-group').removeClass('has-success has-error').addClass('has-error');
			$(e).closest('.help-block').remove();
		},
		success: function (e) {
			e.closest('.form-group').removeClass('has-success has-error');
			e.closest('.help-block').remove();
			e.closest('.help-inline').remove();
		},
		rules: {
			'vEmail': {required: true, email: true,
					remote: {
							url: 'booking-ajax/ajax_validate_email.php',
							type: "post",
							data: {iUserId: ''},
						}
			},
			'vPassword': {required: true, minlength: 6},
			'vPhone': {required: true, phonevalidate: true,
						remote: {
							url: 'booking-ajax/ajax_rider_mobile_new.php',
							type: "post",
							data: {iUserId: ''},
						}
			},
			'vFirstName': {required: true, minlength: 2},
			'vLastName': {required: true, minlength: 2},
		},
		messages: {
			'vEmail': {remote: 'Email address is already exists.'},
			'remember-me': {required: 'Please agree to the Terms & Conditions.'},
			'vPhone': {remote: 'Phone Number is already exists.'},
		},
		submitHandler: function(form){
			userRegister();
		}
	});
	
});