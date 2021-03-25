const firebaseConfig = {
  apiKey: "AIzaSyCd6kdx5a-YBIMksmoMkHRxlqAv268B_Vs",
  authDomain: "cubejacapp.firebaseapp.com",
  databaseURL: "https://cubejacapp.firebaseio.com",
  projectId: "cubejacapp",
  storageBucket: "cubejacapp.appspot.com",
  messagingSenderId: "187163197862",
  appId: "1:187163197862:web:e5e17a31d081612f9c0f67"
};
/*const firebaseConfig = {
  apiKey: "AIzaSyCWDPZwYivp15LizqAa2_Q24dKdn9MWL_Q",
  authDomain: "cubejacweb.firebaseapp.com",
  databaseURL: "https://cubejacweb.firebaseio.com",
  projectId: "cubejacweb",
  storageBucket: "cubejacweb.appspot.com",
  messagingSenderId: "963783182992",
  appId: "1:963783182992:web:4fe1804494c01c894b2615"
};*/

firebase.initializeApp(firebaseConfig);

// Turn off phone auth app verification.
//firebase.auth().settings.appVerificationDisabledForTesting = true;

// Create a Recaptcha verifier instance globally
// Calls submitPhoneNumberAuth() when the captcha is verified
window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier(
  "recaptcha-container",
  {
    size: "invisible",
    callback: function(response) {
      //submitPhoneNumberAuth();
    }
  }
);

  // This function runs when the 'sign-in-button' is clicked
  // Takes the value from the 'phoneNumber' input and sends SMS to that phone number
  function submitPhoneNumberAuth(userphoneNumber) {
    var appVerifier = window.recaptchaVerifier;
    firebase
      .auth()
      .signInWithPhoneNumber(userphoneNumber, appVerifier)
      .then(function(confirmationResult) {
      	//return confirmationResult.confirm();
        window.confirmationResult = confirmationResult;
        /*console.log(window.confirmationResult);*/
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
      })
      .catch(function(error) {
        console.log(error);
      });
  }

  //This function runs everytime the auth state changes. Use to verify if the user is logged in
	firebase.auth().onAuthStateChanged(function(user) {
    if (user) {
      console.log("USER LOGGED IN");
    } else {
      // No user is signed in.
      console.log("USER NOT LOGGED IN");
    }
  });