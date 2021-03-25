<script>
const firebaseConfig = {
    apiKey: "AIzaSyAm29d8zETW6WC8zuNIK4YIYO2bBftZPOs",
    authDomain: "cubeproducts-fb147.firebaseapp.com",
    databaseURL: "https://cubeproducts-fb147.firebaseio.com",
    projectId: "cubeproducts-fb147",
    storageBucket: "cubeproducts-fb147.appspot.com",
    messagingSenderId: "144179964159",
    appId: "1:144179964159:web:5521b68b0fb854d38084ca",
    measurementId: "G-WTGXS1NR3C"
  };
firebase.initializeApp(firebaseConfig);

// Turn off phone auth app verification.
/*firebase.auth().settings.appVerificationDisabledForTesting= true;*/ 

// Create a Recaptcha verifier instance globally
// Calls submitPhoneNumberAuth() when the captcha is verified
  window.onload = function() {
    // Listening for auth state changes.
    //This function runs everytime the auth state changes. Use to verify if the user is logged in
    firebase.auth().onAuthStateChanged(function(user) {
      if (user) {
       firebase.auth().signOut().then(function() {
          // Sign-out successful.
        }, function(error) {
          // An error happened.
        });
      } else {
        // No user is signed in.
        console.log("USER NOT LOGGED IN");
      }
    });


    // [START appVerifier]
    // window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container-new', {
    //   'size': 'nornal',
    //   'callback': function(response) {
    //     // reCAPTCHA solved, allow signInWithPhoneNumber.
    //     submitPhoneNumberAuth();
    //   }
    // });
    // [END appVerifier]

   /* recaptchaVerifier.render().then(function(widgetId) {
      window.recaptchaWidgetId = widgetId;
    });

    var recaptchaResponse = grecaptcha.getResponse(window.recaptchaWidgetId);*/

    //console.log(recaptchaResponse);
  };

  function initReCaptcha()
  {
    // [START appVerifier]
    window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container-new', {
      'size': 'normal',
      'callback': function(response) {
        // reCAPTCHA solved, allow signInWithPhoneNumber.
        //submitPhoneNumberAuth();
      }
    });
    // [END appVerifier]
    recaptchaVerifier.render().then(function(widgetId) {
      window.recaptchaWidgetId = widgetId;
    });
  }
  /**
   * Re-initializes the ReCaptacha widget.
   */
  function resetReCaptcha() {
    if (typeof grecaptcha !== 'undefined'
        && typeof window.recaptchaWidgetId !== 'undefined') {
      grecaptcha.reset(window.recaptchaWidgetId);
    }
  }

  // This function runs when the 'sign-in-button' is clicked
  // Takes the value from the 'phoneNumber' input and sends SMS to that phone number
  function submitPhoneNumberAuth(userphoneNumber) {
    var appVerifier = window.recaptchaVerifier;
    firebase
      .auth()
      .signInWithPhoneNumber(userphoneNumber, appVerifier)
      .then(function(confirmationResult) {
      	//return confirmationResult.confirm();
        //console.log(confirmationResult);
        window.confirmationResult = confirmationResult;
       if(window.confirmationResult){
          //resetReCaptcha();
          show_alert(languagedata['LBL_SIGNUP_PHONE_VERI'],verifysmscontent,languagedata['LBL_BTN_VERIFY_TXT'],languagedata['LBL_CANCEL_TXT'],'',function (btn_id) {
                if(btn_id==0) {
                    submitPhoneNumberAuthCode();
                } else if(btn_id==1){
                    $(".custom-modal-first-div").removeClass("active");
                    $(".pay-card").removeClass("tab-disable");
                    return false;
                } else {
                    alert("Please Verify Phone Number.");$(".pay-card").removeClass("tab-disable");return false;
                }
            },false);
       }
      })
      .catch(function(error) {
          $.each(error, function(k, v){
            if(v == "auth/invalid-phone-number"){
              show_alert(languagedata['LBL_SIGNUP_PHONE_VERI'],languagedata['LBL_PHONE_VALID_MSG'],'',languagedata['LBL_BTN_OK_TXT'],'');
              //$("#errormsg").html(languagedata['LBL_PHONE_VALID_MSG']).show();  
              $(".pay-card").removeClass("tab-disable");
              return false;
            }
            if(v == "auth/missing-phone-number"){
              show_alert(languagedata['LBL_SIGNUP_PHONE_VERI'],languagedata['LBL_PHONE_VALID_MSG'],'',languagedata['LBL_BTN_OK_TXT'],'');
              //$("#errormsg").html(languagedata['LBL_PHONE_VALID_MSG']).show();  
              $(".pay-card").removeClass("tab-disable");
              return false;
            }
            if(v == "auth/too-many-requests"){
              show_alert(languagedata['LBL_SIGNUP_PHONE_VERI'],error.message,'',languagedata['LBL_BTN_OK_TXT'],'');
              $(".pay-card").removeClass("tab-disable");
              return false;
            }

          });
      });
  }

  // This function runs when the 'confirm-code' button is clicked
  // Takes the value from the 'code' input and submits the code to verify the phone number
  // Return a user object if the authentication was successful, and auth is complete
  function submitPhoneNumberAuthCode() {
    $('.site-loader').addClass('active');
    var code = $("#verificationcode").val();
    if(code != ""){
      confirmationResult
        .confirm(code)
        .then(function(result) {
           $("#errormsg").html("");
            var dataSmsverify = {
                "tSessionId": '<?= $Datauser[0]['tSessionId'] ?>',
                "GeneralMemberId": '<?= $Datauser[0]['iUserId'] ?>',
                "GeneralUserType": 'Passenger',
                "MobileNo":'<?= $register_user_data[0]['vPhoneCode']. $register_user_data[0]['vPhone']; ?>',
                "type": 'sendVerificationSMS',
                "iMemberId": '<?= $iUserId ?>',
                "UserType": 'Passenger',
                "REQ_TYPE": 'PHONE_VERIFIED',
                "vTimeZone": '<?= $vTimeZone ?>',
            };
            dataSmsverify = $.param(dataSmsverify);
            $.ajax({
                    type: "POST",
                    url: "<?= $tconfig["tsite_url"] . ManualBookingAPIUrl; ?>",
                    data: dataSmsverify,
                    dataType: 'json',
                    success: function (dataHtml)
                    {
                        $('.site-loader').removeClass('active');
                        show_alert(languagedata["LBL_SIGNUP_PHONE_VERI"],languagedata[dataHtml.message],languagedata["LBL_BTN_OK_TXT"],"","",function (btn_id) {
                            if(btn_id == 0){
                                $(".pay-card").removeClass("tab-disable");
                                $("#flex-row-error").html(" ");
                                var submitpaymentmethod = $("#payment").val();
                                if(submitpaymentmethod == "Cash"){
                                    $(".pay-card").removeClass("active").addClass("passed");
                                    $(".PaymentIcon").show();
                                    $("#payment").val("Cash");
                                    submitorderCard("","","Cash");
                                    return false;
                                } else {
                                    var paymentMethod = $(".submitplaceorderCard").attr("data-method");
                                    $(".pay-card").removeClass("active").addClass("passed");
                                    $(".PaymentIcon").show();
                                    $("#payment").val("Card");
                                    submitorderCard(paymentMethod);
                                    return false;
                                }
                            }
                        });
                        return false;
                    }
                });
        })
        .catch(function(error) {
          $('.site-loader').removeClass('active');
          $.each(error, function(k, v){
            if(v == "auth/invalid-verification-code"){
              $("#errormsg").html(languagedata['LBL_INVALID_VERIFICATION_CODE_ERROR']).show(); 
              $(".pay-card").removeClass("tab-disable");
              return false;
            }

            if(v == "auth/invalid-verification-code"){
              $("#errormsg").html(languagedata['LBL_ENTER_VERIFICATION_CODE']).show();  
              $(".pay-card").removeClass("tab-disable");
              return false;
            }

            if(v == "auth/invalid-phone-number"){
              $("#errormsg").html(languagedata['LBL_PHONE_VALID_MSG']).show();  
              $(".pay-card").removeClass("tab-disable");
              return false;
            }

            if(v == "auth/missing-phone-number"){
              $("#errormsg").html(languagedata['LBL_PHONE_VALID_MSG']).show();  
              $(".pay-card").removeClass("tab-disable");
              return false;
            }
          });
        });
    } else {
      $('.site-loader').removeClass('active');
      $("#errormsg").html(languagedata['LBL_ENTER_VERIFICATION_CODE']).show();  
      $(".pay-card").removeClass("tab-disable");
      return false;
    }
  }

</script>