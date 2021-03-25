/**
 *-----------------------------------------------------------------
 * Additional validation patterns
 *-----------------------------------------------------------------
 **/
$(function () {
    //  alert(_system_script);
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
    $.validator.addMethod('maxStrict', function (value, el, param) {
        return value <= param;
    });

    $.validator.addMethod("greaterThan",
        function (value, element, param) {
            var $min = $(param);
            if (this.settings.onfocusout) {
                $min.off(".validate-greaterThan").on("blur.validate-greaterThan", function () {
                    $(element).valid();
                });
            }
            if (param != '') {
                return parseInt(value) > parseInt($min.val());
            } else {
                return true;
            }
        }, "Max must be greater than min");

    /*    $.validator.addMethod("noSpace", function(value, element) { 
     return value.indexOf("") < 0 && value != ""; 
     }, "Password should not contain whitespace.");*/

    $.validator.addMethod("noSpace", function (value, element) {
        return this.optional(element) || /^\S+$/i.test(value);
    }, "Password should not contain whitespace.");

});

$(function () {

    //Admin Start
    if (_system_script == 'Admin') {
        var errormessage;
        if ($('#_admin_form').length !== 0) {
            $('#_admin_form').validate({
                ignore: 'input[type=hidden],:hidden',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vFirstName: { required: true, minlength: 2, maxlength: 30 },
                    vLastName: { minlength: 2, maxlength: 30 },
                    vEmail: {
                        required: true, email: true,
                        remote: {
                            url: _system_admin_url + 'ajax_validate_email.php',
                            type: "post",
                            data: {
                                iAdminId: function () {
                                    return $("#iAdminId").val();
                                }
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Email address is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Email address is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    vPassword: {
                        required: function () {
                            return $("#actionOf").val() == "Add";
                        }, noSpace: true, minlength: 6, maxlength: 16
                    },
                    fHotelServiceCharge: { number: true },
                    vContactNo: {
                        remote: {
                            url: _system_admin_url + 'ajax_validate_phone_admin.php',
                            type: "post",
                            data: {
                                iAdminId: function () {
                                    return $("#iAdminId").val();
                                }, vCountry: function () {
                                    return $("#vCountry").val();
                                }
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted' && iGroupId == '4') {
                                    errormessage = "Phone number is Inactive/Deleted. Please active again.";
                                    alert(errormessage);
                                    return false;
                                } else if (response == 'false' && iGroupId == '4') {
                                    errormessage = "Phone number is already exist.";
                                    alert(errormessage);
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    //vPhone: {required: true, phonevalidate: true},
                    iGroupId: { required: true }
                },
                messages: {
                    vFirstName: {
                        required: 'This field is required.',
                        minlength: 'First Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vLastName: {
                        required: 'This field is required.',
                        minlength: 'Last Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vEmail: {
                        required: 'This field is required.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vPassword: {
                        required: 'This field is required.',
                        minlength: 'Password at least 6 characters long.',
                        maxlength: 'Please enter less than 16 characters.'
                    },
                    /*vPhone: {
                     required: 'This field is required.',
                     phonevalidate: 'Please enter valid Phone Number.'
                     },*/
                    vContactNo: {
                        remote: function () {
                            return errormessage;
                        }
                    },
                    iGroupId: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    $("#vCountry").prop('disabled',false);
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Admin End

    // profile start
    if (_system_script == 'profile') {
        var errormessage;
        if ($('#_admin_form').length !== 0) {
            $('#_admin_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vFirstName: { required: true, minlength: 2, maxlength: 30 },
                    vLastName: { required: true, minlength: 2, maxlength: 30 },
                    vEmail: {
                        required: true, email: true,
                        remote: {
                            url: _system_admin_url + 'ajax_validate_email.php',
                            type: "post",
                            data: {
                                iAdminId: function () {
                                    return $("#iAdminId").val();
                                }
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Email address is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Email address is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    vPassword: {
                        required: function () {
                            return $("#actionOf").val() == "Add";
                        }, noSpace: true, minlength: 6, maxlength: 16
                    },
                    fHotelServiceCharge: { number: true },
                    vContactNo: { required: true, minlength: 3, digits: true }, // phonevalidate: true
                    iGroupId: { required: true }
                },
                messages: {
                    vFirstName: {
                        required: 'This field is required.',
                        minlength: 'First Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vLastName: {
                        required: 'This field is required.',
                        minlength: 'Last Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vEmail: {
                        required: 'This field is required.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vPassword: {
                        required: 'This field is required.',
                        minlength: 'Password at least 6 characters long.',
                        maxlength: 'Please enter less than 16 characters.'
                    },
                    vContactNo: {
                        required: 'This field is required.',
                        phonevalidate: 'Please enter valid Phone Number.'
                    },
                    iGroupId: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    $("#vCountry").prop('disabled',false);
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    // profile end
	// map api service action form validation
	if ($('#_map_api_setting_action_form').length !== 0) {
        $('#_map_api_setting_action_form').validate({
            ignore: 'input[type=hidden]',
            errorClass: 'help-block',
            errorElement: 'span',
            errorPlacement: function (error, e) {
                e.parents('.row > div').append(error);
            },
            highlight: function (e) {
                $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                $(e).closest('.help-block').remove();
            },
            success: function (e) {
                e.closest('.row').removeClass('has-success has-error');
                e.closest('.help-block').remove();
                e.closest('.help-inline').remove();
            },
            rules: {
                vUsageOrder: {
                    required: true,
                    remote: {
                        url: _system_admin_url + 'ajax_validate_usage_order.php',
                        type: "post",
                        data: {
                            usageOrder: function () {
                                return $("#vUsageOrder").val();
                            }, sid: function () {
                                return $("#sid").val();
                            }, id: function () {
                                return $("#id").val();
                            },map_api_setting: function () {
                                return "Yes";
                            }
                        },
                        dataFilter: function (response) {							
                            if (response > 0) {
                                errormessage = "Usage order is assigned, please select different.";
                                return false;
                            } else {
                                return true;
                            }
                        },
                        async: false
                    }
                },
                eStatus: { required: true }
            },
            messages: {               
                vUsageOrder: {
                    required: 'This field is required.',
                    remote: 'Usage order is assigned, please select different.'
                },
                eStatus: {
                    required: 'This field is required.'
                }
            },
            submitHandler: function (form) {
								if ($(form).valid()){
										form.submit();
								}
                return false; // prevent normal form posting
            }
        });
    }
    // Map API Mongo Auth Places start
    if ($('#_authmongoplaces_form').length !== 0) {
        $('#_authmongoplaces_form').validate({
            ignore: 'input[type=hidden]',
            errorClass: 'help-block',
            errorElement: 'span',
            errorPlacement: function (error, e) {
                e.parents('.row > div').append(error);
            },
            highlight: function (e) {
                $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                $(e).closest('.help-block').remove();
            },
            success: function (e) {
                e.closest('.row').removeClass('has-success has-error');
                e.closest('.help-block').remove();
                e.closest('.help-inline').remove();
            },
            rules: {
                vTitle: { required: true },
                // vServiceId: {required: true},
                vAuthKey: { 
                    required: true,
                    noSpace: true,
					// remote: {
                        // url: _system_admin_url + 'ajax_validate_auth_key.php',
                        // type: "post",
                        // data: {
                            // vAuthKey: function () {
                                // return $("#vAuthKey").val();
                            // }, vServiceAccountId: function () {
                                // return $("#vServiceAccountId").val();
                            // }, id: function () {
                                // return $("#id").val();
                            // }, search_address: function () {
                                // return $("#search_address").val();
                            // }
                        // },
                        // dataFilter: function (response) {	
                            // if (response == false) {
                                // return false;
                            // } else {
                                // return true;
                            // }
                        // },
                        // async: false
                    // }
                },
                vUsageOrder: {
                    required: true,
                    remote: {
                        url: _system_admin_url + 'ajax_validate_usage_order.php',
                        type: "post",
                        data: {
                            usageOrder: function () {
                                return $("#vUsageOrder").val();
                            }, sid: function () {
                                return $("#sid").val();
                            }, id: function () {
                                return $("#id").val();
                            }
                        },
                        dataFilter: function (response) {							
                            if (response > 0) {
                                errormessage = "Usage order is assigned, please select different.";
                                return false;
                            } else {
                                return true;
                            }
                        },
                        async: false
                    }
                },
                eStatus: { required: true }
            },
            messages: {
                vTitle: {
                    required: 'This field is required.'
                },
                vServiceId: {
                    required: 'This field is required.'
                },
                vAuthKey: {
                    required: 'This field is required.',
                    noSpace: 'Auth key should not contain whitespace.',
					remote: 'auth key is invalid.'
                },
                vUsageOrder: {
                    required: 'This field is required.',
                    remote: 'Usage order is assigned, please select different.'
                },
                eStatus: {
                    required: 'This field is required.'
                }
            },
            submitHandler: function (form) {
					var resultAuth = 0;
                       
						
								if ($(form).valid()){
									 $.ajax({
										url: _system_admin_url + 'ajax_validate_auth_key.php',
										type: 'post',
										async:false,
										data: {
											vAuthKey: function () {
												return $("#vAuthKey").val();
											}, vServiceAccountId: function () {
												return $("#vServiceAccountId").val();
											}, id: function () {
												return $("#id").val();
											}, search_address: function () {
												return $("#search_address").val();
											}
										},
										dataFilter: function (response) {	
											if (response == 1) {
												resultAuth = 1;
											}
										}
									});
									if(resultAuth == 1){
										$('#vAuthKey-error').text('')
										form.submit();
									}else{
										$('#vAuthKey-error').text('auth key is invalid.')
										return false;
									}
								}
                return false; // prevent normal form posting
            }
        });
    }

    //vehicles Start
    if (_system_script == 'Vehicle') {
        if ($('#_vehicle_form').length !== 0) {
            $('#_vehicle_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    iMakeId: { required: true },
                    iModelId: { required: true },
                    iYear: { required: true },
                    vLicencePlate: { required: true },
                    //iCompanyId: { required: true },
                    iDriverId: { required: true },
                    'vCarType[]': { required: true }
                },
                messages: {
                    iMakeId: {
                        required: 'This field is required.'
                    },
                    iModelId: {
                        required: 'This field is required.'
                    },
                    iYear: {
                        required: 'This field is required.'
                    },
                    vLicencePlate: {
                        required: 'This field is required.'
                    },
                    /*iCompanyId: {
                        required: 'This field is required.'
                    },*/
                    iDriverId: {
                        required: 'This field is required.'
                    },
                    'vCarType[]': {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //vehiclesp End

    //Coupon Start
    if (_system_script == 'Coupon') {
        if ($('#_coupon_form').length !== 0) {
            $('#_coupon_form').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vCouponCode: { required: true },
                    tDescription: { required: true, minlength: 2 },
                    fDiscount: {
                        required: true, number: true, maxStrict: function () {
                            if ($("#eType").val() == "percentage") {
                                return 100;
                            } else {
                                return 3000;
                            }
                        }
                    },
                    iUsageLimit: { required: true, number: true },
                    dActiveDate: {
                        required: function () {
                            return $("input[name='eValidityType']:checked").val() == "Defined";
                        }
                    },
                    dExpiryDate: {
                        required: function () {
                            return $("input[name='eValidityType']:checked").val() == "Defined";
                        }},
                    vPromocodeType: {required: true},
                },
                messages: {
                    vCouponCode: {
                        required: 'This field is required.',
                    },
                    tDescription: {
                        required: 'This field is required.',
                        minlength: 'Description at least 2 characters long.'
                    },
                    fDiscount: {
                        required: 'This field is required.',
                        maxStrict: function () {
                            if ($("#eType").val() == "percentage") {
                                return 'Please enter between 1 to 100 only.';
                            } else {
                                return 'Please enter between 1 to 3000 only.';
                            }
                        }
                    },
                    iUsageLimit: {
                        required: 'This field is required.'
                    },
                    dActiveDate: {
                        required: 'This field is required.'
                    },
                    dExpiryDate: {
                        required: 'This field is required.'
                    },
                    vPromocodeType: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Coupon End

    //DeliverAllStore Start
    if (_system_script == 'DeliverAllStore') {
        var errormessage;
        if ($('#_company_form').length !== 0) {
            $('#_company_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                /*errorPlacement: function (error, e) {
                 e.parents('.row > div').append(error);
                 },*/
                errorPlacement: function (error, element) {
                    if (element.attr("name") == "cuisineId[]") {
                        error.insertAfter(".CuisineClass");
                    } else if (element.attr("name") == "vFromMonFriTimeSlot1") {
                        error.appendTo(".FromError1");
                    } else if (element.attr("name") == "vToMonFriTimeSlot1") {
                        error.appendTo(".ToError1");
                    } else if (element.attr("name") == "vFromSatSunTimeSlot1") {
                        error.appendTo(".FromError2");
                    } else if (element.attr("name") == "vToSatSunTimeSlot1") {
                        error.appendTo(".ToError2");
                    } else {
                        error.insertAfter(element);
                    }
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vCompany: { required: true, minlength: 2, maxlength: 100 },
                    iServiceId: { required: true },
                    iMaxItemQty: { required: true, digits: true, min: 1 },
                    fPrepareTime: { required: true, digits: true, min: 1 },
                    fOfferAppyType: { required: true },
                    fPricePerPerson: { required: true, number: true, digits: true, min: 1 },
                    vEmail: {
                        required: true, email: true,
                        /*remote: {
                            url: _system_admin_url + 'ajax_validate_email.php',
                            type: "post",
                            data: {iCompanyId: function () {
                                    return $("#iCompanyId").val();
                                }},
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Email address is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Email address is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }*/
                    },
                    vPassword: {
                        required: function () {
                            return $("#actionOf").val() == "Add";
                        }, noSpace: true, minlength: 6, maxlength: 16
                    },
                    vPhone: {
                        required: true, minlength: 3, digits: true, //phonevalidate: true,
                        /*remote: {
                            url: _system_admin_url + 'ajax_validate_phone.php',
                            type: "post",
                            data: {iCompanyId: function () {
                                    return $("#iCompanyId").val();
                                }},
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Phone number is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Phone number is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }*/
                    },
                    vCaddress: { required: true, minlength: 2 },
                    vZip: { required: true, minlength: 2 },
                    vLang: { required: true },
                    vContactName: { required: true },
                    'cuisineId[]': { required: true },
                    fMinOrderValue: { number: true },
                    fPackingCharge: { number: true },
                    vCountry: { required: true },
                    fOfferAmt: {
                        number: function () {
                            return $("#fOfferAmt").prop('required');
                        },
                        min: function () {
                            return $("#fOfferAmt").prop('required');
                            //    return $("#fOfferAmtDiv").is(":visible");
                        },
                        max: function () {
                            if ($("#fOfferAmt").prop('required') == true && $("#fOfferType").val() == 'Percentage') {
                                return 100;
                            }
                        }
                    },
                    fTargetAmt: {
                        number: function () {
                            if ($("#fTargetAmt").prop('required') == true && $("#fOfferType").val() != 'Percentage') {
                                return true;
                            }
                        },
                        greaterThan: function () {
                            if ($("#fTargetAmt").prop('required') == true && $("#fOfferType").val() != 'Percentage') {
                                return '#fOfferAmt';
                            } else {
                                return '';
                            }
                        },
                        min: function () {
                            return $("#fTargetAmt").prop('required');
                        }
                    },
                    fMaxOfferAmt: { number: true },
                    vRestuarantLocation: { required: true }
                },
                messages: {
                    vCompany: {
                        minlength: 'Name at least 2 characters long.',
                        maxlength: 'Please enter less than 100 characters.'
                    },
                    iMaxItemQty: {
                        required: 'This field is required.',
                        digits: 'Please enter numeric value only',
                        min: 'Please Enter Value Greater than Zero.',
                    },
                    vEmail: {
                        required: 'This field is required.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vPassword: {
                        required: 'This field is required.',
                        minlength: 'Password at least 6 characters long.',
                        maxlength: 'Please enter less than 16 characters.'
                    },
                    vPhone: {
                        required: 'This field is required.',
                        minlength: 'Please enter at least three Number.',
                        digits: 'Please enter proper mobile number.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vCaddress: {
                        required: 'This field is required.'
                    },
                    vZip: {
                        required: 'This field is required.'
                    },
                    vLang: {
                        required: 'This field is required.'
                    },
                    vContactName: {
                        required: 'This field is required.'
                    },
                    'cuisineId[]': {
                        required: 'This field is required.'
                    },
                    vCountry: {
                        required: 'This field is required.'
                    },
                    fOfferAmt: {
                        min: 'Please Enter Value Greater than Zero.'
                    },
                    fTargetAmt: {
                        greaterThan: 'Target Amount must be greater than Offer amount for Flat offer type.',
                        min: 'Please Enter Value Greater than Zero.'
                    },
                    vRestuarantLocation: {
                        required: 'This field is required.'
                    },
                    fPrepareTime: {
                        required: 'This field is required.',
                        digits: 'Please enter numeric value only.',
                        min: 'Please Enter Value Greater than Zero.',
                    },
                    fPricePerPerson: {
                        required: 'This field is required.',
                        digits: 'Please enter numeric value only.',
                        min: 'Please Enter Value Greater than Zero.',
                    }
                },
                submitHandler: function (form) {
                    $("#vCountry").prop('disabled',false);
                    $("#vLang").prop('disabled',false);
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //DeliverAllStore End


    //Food Menu Item Validation  Start
    if (_system_script == 'MenuItems') {
        var errormessage;
        if ($('#menuItem_form').length !== 0) {
            $('#menuItem_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block error',
                errorElement: 'span',
                /* errorPlacement: function(error, element) {
                 if (element.attr("name") == "cuisineId[]")
                 {
                 error.insertAfter(".CuisineClass");
                 } else {
                 error.insertAfter(element);
                 }
                 },*/
                onkeyup: function (element) {
                    $(element).valid()
                },
                highlight: function (e) {
                    if ($(e).attr("name") == "OptPrice[]" || $(e).attr("name") == "AddonOptions[]" || $(e).attr("name") == "BaseOptions[]" || $(e).attr("name") == "AddonPrice[]") {
                        $(e).closest('.row .form-group').removeClass('has-success has-error').addClass('has-error');
                    } else {
                        $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    }
                    //$(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row .form-group').removeClass('has-success has-error');
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    iCompanyId: { required: true },
                    iFoodMenuId: { required: true },
                    fPrice: { required: true, number: true },
                    fOfferAmt: { number: true },
                    'BaseOptions[]': { required: true },
                    'OptPrice[]': { required: true, number: true },
                    'AddonOptions[]': { required: true },
                    'AddonPrice[]': { required: true, number: true }
                },
                messages: {
                    iCompanyId: {
                        required: 'This field is required.'
                    },
                    iFoodMenuId: {
                        required: 'This field is required.'
                    },
                    fPrice: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Food Menu Item Validation End

    //Company Start
    if (_system_script == 'Company') {
        var errormessage;
        if ($('#_company_form').length !== 0) {
            $('#_company_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vCompany: { required: true, minlength: 2, maxlength: 30 },
                    vEmail: {
                        required: true, email: true,
                        remote: {
                            url: _system_admin_url + 'ajax_validate_email.php',
                            type: "post",
                            data: {
                                iCompanyId: function () {
                                    return $("#iCompanyId").val();
                                }
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Email address is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Email address is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    vPassword: {
                        required: function () {
                            return $("#actionOf").val() == "Add";
                        }, noSpace: true, minlength: 6, maxlength: 16
                    },
                    vPhone: {
                        required: true, minlength: 3, digits: true, //phonevalidate: true,
                        remote: {
                            url: _system_admin_url + 'ajax_validate_phone.php',
                            type: "post",
                            data: {
                                iCompanyId: function () {
                                    return $("#iCompanyId").val();
                                }
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Phone number is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Phone number is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    vCaddress: { required: true, minlength: 2 },
                    // vCity: {required: true},
                    // vState: {required: true},
                    vZip: { required: true, minlength: 2 },
                    vLang: { required: true },
                    // vVatNum: {required: true, minlength: 2},
                    vCountry: { required: true }
                },
                messages: {
                    vCompany: {
                        required: 'This field is required.',
                        minlength: 'Company Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vEmail: {
                        required: 'This field is required.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vPassword: {
                        required: 'This field is required.',
                        minlength: 'Password at least 6 characters long.',
                        maxlength: 'Please enter less than 16 characters.'
                    },
                    vPhone: {
                        required: 'This field is required.',
                        minlength: 'Please enter at least three Number.',
                        digits: 'Please enter proper mobile number.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vCaddress: {
                        required: 'This field is required.'
                    },
                    vZip: {
                        required: 'This field is required.'
                    },
                    vLang: {
                        required: 'This field is required.'
                    },
                    // vCity: {
                    // required: 'City is required.'
                    // },
                    // vState: {
                    // required: 'State is required.'
                    // },
                    /*vVatNum: {
                     required: 'Vat Number is required.'
                     },*/
                    vCountry: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    $("#vCountry").prop('disabled',false);
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Company End



    /* Organization Module */

    if (_system_script == 'Organization') {
        var errormessage;
        if ($('#_organization_form').length !== 0) {
            $('#_organization_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {

                    vCompany: { required: true, minlength: 2, maxlength: 30 },
                    vEmail: {
                        required: true, email: true,
                        /* 06-09-219 check email,phone validation using member function added by Rs start(check phone number using country) */
                        /*remote: {
                            url: _system_admin_url + 'ajax_validate_email.php',
                            type: "post",
                            data: {iOrganizationId: function () {
                                    return $("#iOrganizationId").val();
                                }},
                            dataFilter: function (response) {

                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Email address is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Email address is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }*/
                    },

                    vPassword: {
                        required: function () {
                            return $("#actionOf").val() == "Add";
                        }, noSpace: true, minlength: 6, maxlength: 16
                    },
                    vPhone: {
                        required: true, minlength: 3, digits: true, //phonevalidate: true,
                        /* 06-09-219 check email,phone validation using member function added by Rs start(check phone number using country) */
                        /*remote: {
                            url: _system_admin_url + 'ajax_validate_phone.php',
                            type: "post",
                            data: {iOrganizationId: function () {
                                    return $("#iOrganizationId").val();
                                }},
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Phone number is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Phone number is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }*/
                    },
                    vCaddress: { required: true, minlength: 2 },
                    // vCity: {required: true},
                    // vState: {required: true},
                    vZip: { required: true, minlength: 2 },
                    vLang: { required: true },
                    // vVatNum: {required: true, minlength: 2},
                    vCountry: { required: true },
                    ePaymentBy: { required: true },
                    iUserProfileMasterId: { required: true },
                    vImage: { accept: "jpg,jpeg,png,gif,bmp" }
                },
                messages: {
                    vCompany: {
                        required: 'This field is required.',
                        minlength: 'Organization Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vEmail: {
                        required: 'This field is required.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vPassword: {
                        required: 'This field is required.',
                        minlength: 'Password at least 6 characters long.',
                        maxlength: 'Please enter less than 16 characters.'
                    },
                    vPhone: {
                        required: 'This field is required.',
                        minlength: 'Please enter at least three Number.',
                        digits: 'Please enter proper mobile number.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vCaddress: {
                        required: 'This field is required.'
                    },
                    vZip: {
                        required: 'This field is required.'
                    },
                    vLang: {
                        required: 'This field is required.'
                    },
                    // vCity: {
                    // required: 'City is required.'
                    // },
                    // vState: {
                    // required: 'State is required.'
                    // },
                    /*vVatNum: {
                     required: 'Vat Number is required.'
                     },*/
                    vCountry: {
                        required: 'This field is required.'
                    },
                    ePaymentBy: {
                        required: 'This field is required.'
                    },
                    iUserProfileMasterId: {
                        required: 'This field is required.'
                    },

                    vImage: { accept: 'Please Upload valid file format for Image. Valid formats are jpg,jpeg,png,gif,bmp' }
                },
                submitHandler: function (form) {
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }

    /* Organization Module */


    //Rider Start
    if (_system_script == 'Rider') {
        var errormessage;
        if ($('#_rider_form').length !== 0) {
            $('#_rider_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vName: { required: true, minlength: 2, maxlength: 30 },
                    vLastName: { required: true, minlength: 2, maxlength: 30 },
                    vEmail: {
                        required: true, email: true,
                        remote: {
                            url: _system_admin_url + 'ajax_validate_email.php',
                            type: "post",
                            data: {
                                iUserId: function () {
                                    return $("#iUserId").val();
                                }
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Email address is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Email address is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    vImgName: { required: false, accept: "image/*" },
                    vPassword: {
                        required: function () {
                            return $("#actionOf").val() == "Add";
                        }, noSpace: true, minlength: 6, maxlength: 16
                    },
                    vCountry: { required: true },
                    // eGender: {required: true},
                    vPhone: {
                        required: true, minlength: 3, digits: true,
                        remote: {
                            url: _system_admin_url + 'ajax_validate_phone.php',
                            type: "post",
                            data: {
                                iUserId: function () {
                                    return $("#iUserId").val();
                                }
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Phone Number is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Phone Number is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    vLang: { required: true },
                    vCurrencyPassenger: { required: true }
                },
                messages: {
                    vName: {
                        required: 'This field is required.',
                        minlength: 'First Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vLastName: {
                        required: 'This field is required.',
                        minlength: 'Last Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vEmail: {
                        required: 'This field is required.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vPassword: {
                        required: 'This field is required.',
                        minlength: 'Password at least 6 characters long.',
                        maxlength: 'Please enter less than 16 characters.'
                    },
                    vCountry: {
                        required: 'This field is required.'
                    },
                    vImgName: { accept: "Please select only image file." },
                    vPhone: {
                        required: 'This field is required.',
                        minlength: 'Please enter at least three Number.',
                        digits: 'Please enter proper mobile number.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vLang: {
                        required: 'This field is required.'
                    },
                    vCurrencyPassenger: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //rider End

    //make Start
    if (_system_script == 'Make') {
        if ($('#_make_form').length !== 0) {
            $('#_make_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vMake: { required: true, minlength: 2 }
                },
                messages: {
                    vMake: {
                        required: 'This field is required.',
                        minlength: 'Make Name at least 2 characters long.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //make End

    //model Start
    if (_system_script == 'Model') {
        if ($('#_model_form').length !== 0) {
            $('#_model_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vTitle: { required: true, minlength: 2 }
                },
                messages: {
                    vTitle: {
                        required: 'This field is required.',
                        minlength: 'Model Name at least 2 characters long.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //model End

    //country Start
    //Country
    if (_system_script == 'country') {

        if ($('#_country_form').length !== 0) {
            $('#_country_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vCountry: { required: true, minlength: 2 },
                    vCountryCode: { required: true },
                    // vCountryCodeISO_3: {required: true},
                    vPhoneCode: { required: true }
                },
                messages: {
                    vCountry: {
                        required: 'This field is required.',
                        minlength: 'Country Name at least 2 characters long.'
                    },
                    vCountryCode: {
                        required: 'This field is required.',
                        minlength: 'Country Code Name at least 2 characters long.'
                    },
                    // vCountryCodeISO_3: {
                    // required: 'CountryCodeISO is required.'
                    // },
                    vPhoneCode: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //country End


    //State Start
    //State
    if (_system_script == 'state') {
        if ($('#_state_form').length !== 0) {
            $('#_state_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vCountry: { required: true },
                    vState: { required: true },
                    vStateCode: { required: true },
                },
                messages: {
                    vCountry: {
                        required: 'This field is required.',
                    },
                    vState: {
                        required: 'This field is required.'
                    },
                    vStateCode: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //State End


    //State Start
    if (_system_script == 'city') {
        if ($('#_city_form').length !== 0) {
            $('#_city_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vCountry: { required: true },
                    vState: { required: true },
                    vCity: { required: true },
                },
                messages: {
                    vCountry: {
                        required: 'This field is required.',
                    },
                    vState: {
                        required: 'This field is required.'
                    },
                    vCity: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //State End

    //faq Start
    if (_system_script == 'FAQ') {
        //alert('hi');
        if ($('#_faq_form').length !== 0) {
            $('#_faq_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vTitle_EN: { required: true, minlength: 2 }
                },
                messages: {
                    vTitle_EN: {
                        required: 'This field is required.',
                        minlength: 'English Question at least 2 characters long.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //faq End

    //FAQ_CAT Start
    if (_system_script == 'FAQ_CAT') {
        if ($('#_faq_cat_form').length !== 0) {
            $('#_faq_cat_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vTitle_EN: { required: true, minlength: 2 }
                },
                messages: {
                    vTitle_EN: {
                        required: 'This field is required.',
                        minlength: 'English label at least 2 characters long.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //FAQ_CAT End

    //Pages Start
    if (_system_script == 'Pages') {
        if ($('#_page_form').length !== 0) {
            $('#_page_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vPageTitle_EN: { required: true, minlength: 2 }
                },
                messages: {
                    vPageTitle_EN: {
                        required: 'This field is required.',
                        minlength: 'PageTitle Value at least 2 characters long.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Pages End

    //Languages Start
    if (_system_script == 'languages') {
        //alert('1111');
        if ($('#_languages_form').length !== 0) {
            $('#_languages_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vLabel: { required: true, minlength: 2 },
                    vValue_EN: { required: true, minlength: 2 }
                },
                messages: {
                    vLabel: {
                        required: 'This field is required.',
                        minlength: 'Language Label at least 2 characters long.'
                    },
                    vValue_EN: {
                        required: 'This field is required.',
                        minlength: 'English Value at least 2 characters long.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Languages End

    //Languages Other Label
    if (_system_script == 'language_label_other') {
        //alert('1111');
        if ($('#_language_label_other_form').length !== 0) {
            $('#_language_label_other_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vLabel: { required: true, minlength: 2 },
                    vValue_EN: { required: true, minlength: 2 }
                },
                messages: {
                    vLabel: {
                        required: 'This field is required.',
                        minlength: 'Language Label at least 2 characters long.'
                    },

                    vValue_EN: {
                        required: 'This field is required.',
                        minlength: 'English Value at least 2 characters long.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Languages Other Label


    //Driver Start
    if (_system_script == 'Driver') {
        var errormessage;
        if ($('#_driver_form').length !== 0) {
            $('#_driver_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vName: { required: true, minlength: 2, maxlength: 30 },
                    vLastName: { required: true, minlength: 2, maxlength: 30 },
                    vEmail: {
                        required: true, email: true,
                        remote: {
                            url: _system_admin_url + 'ajax_validate_email.php',
                            type: "post",
                            data: {
                                iDriverId: function () {
                                    return $("#iDriverId").val();
                                }
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Email address is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Email address is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    vPassword: {
                        required: function () {
                            return $("#actionOf").val() == "Add";
                        }, noSpace: true, minlength: 6, maxlength: 16
                    },
                    vPhone: {
                        required: true, minlength: 3, digits: true,
                        remote: {
                            url: _system_admin_url + 'ajax_validate_phone.php',
                            type: "post",
                            data: {
                                iDriverId: function () {
                                    return $("#iDriverId").val();
                                }
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Phone Number is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Phone Number is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    vImage: { required: false, accept: 'image/*' }, //, accept: 'image/*'
                    vCountry: { required: true },
                    //iCompanyId: {required: true},
                    //vZip: {required: true}, 
                    // eGender: {required: true},
                    // dBirthDate: {required: true},
                    vDay: { required: true },
                    vMonth: { required: true },
                    //vCaddress: {required: true},
                    vYear: { required: true },
                    vLang: { required: true },
                    vCurrencyDriver: { required: true },
                    vPaymentEmail: { required: false, email: true }
                },
                messages: {
                    vName: {
                        required: 'This field is required.',
                        minlength: 'First Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vLastName: {
                        required: 'This field is required.',
                        minlength: 'Last Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vEmail: {
                        required: 'This field is required.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vPassword: {
                        required: 'This field is required.',
                        minlength: 'Password at least 6 characters long.',
                        maxlength: 'Please enter less than 16 characters.'
                    },
                    vPhone: {
                        required: 'This field is required.',
                        minlength: 'Please enter at least three Number.',
                        digits: 'Please enter proper mobile number.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vCountry: {
                        required: 'This field is required.'
                    },
                    /*iCompanyId: {
                     required: 'Company is required.'
                     },*/
                    /*vZip: {
                     required: 'Zip Code is required.'
                     },*/
                    /* dBirthDate: {
                     required: 'Birth Date is required.'
                     }, */
                    vDay: {
                        required: 'This field is required.'
                    },

                    vMonth: {
                        required: 'This field is required.'
                    },
                    vYear: {
                        required: 'This field is required.'
                    },
                    vLang: {
                        required: 'This field is required.'
                    },
                    /*vCaddress: {
                     required: 'Address is required.'
                     },*/
                    vCurrencyDriver: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Driver End

    //Vehicle Type Start
    /*    if (_system_script == 'VehicleType') {
     if ($('#_vehicleType_form').length !== 0) {
     $('#_vehicleType_form').validate({
     
     ignore: 'input[type=hidden]',
     errorClass: 'help-block',
     errorElement: 'span',
     errorPlacement: function (error, e) {
     e.parents('.row > div').append(error);
     },
     highlight: function (e) {
     $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
     $(e).closest('.help-block').remove();
     },
     success: function (e) {
     e.closest('.row').removeClass('has-success has-error');
     e.closest('.help-block').remove();
     e.closest('.help-inline').remove();
     },
     rules: {
     vVehicleType: {required: true},
     vVehicleType_EN: {required: true},
     fVisitFee: {required: true, number: true},
     fPricePerKM: {required: true, number: true},
     fPricePerMin: {required: true, number: true},
     fPricePerHour: {required: true, number: true},
     },
     messages: {
     vVehicleType: {
     required: 'Vehicle type is required.'
     },
     vVehicleType_EN: {
     required: 'Vehicle type (English) is required.'
     },
     fVisitFee: {
     required: 'Visit fee is required.'
     },
     fPricePerKM: {
     required: 'Price per KM is required.'
     },
     fPricePerMin: {
     required: 'Price per Minute is required.'
     },
     fPricePerHour: {
     required: 'Price per Hour is required.',
     minlength: 'dExpiryDate at least 2 characters long.'
     },
     }
     });
     }
     }*/
    //Vehicle Type End

    //Vehicle Type estimate fare Start
    if (_system_script == 'AdminFareEstimate') {
        if ($('#_vehicleType_esti_form').length !== 0) {
            $('#_vehicleType_esti_form').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    iBaseFare: { required: true, number: true },
                    fPricePerKM: { required: true, number: true },
                    fPricePerMin: { required: true, number: true },
                    iMinFare: { required: true, number: true },
                    fCommision: { required: true, number: true },
                },
                messages: {
                    iBaseFare: {
                        required: 'This field is required.'
                    },
                    iMinFare: {
                        required: 'This field is required.'
                    },
                    fPricePerKM: {
                        required: 'This field is required.'
                    },
                    fPricePerMin: {
                        required: 'This field is required.'
                    },
                    fCommision: {
                        required: 'This field is required.'
                    },
                },
                submitHandler: function (form) {
                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Vehicle Type estimate fare End

});