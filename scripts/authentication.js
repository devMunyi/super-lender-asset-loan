function createaccount(){
    let full_name = $('#full_name').val();
    let email = $('#email_').val();
    let mobile = $('#mobile_').val();
    let password = $('#password_').val();
let params = "full_name="+full_name+"&mobile="+mobile+"&email="+email+"&password="+password;
dbaction("/action/signup",params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("SUCCESS", "TOAST", ".feedback_", feed, "4");

})

}

function login(){

    toggleBtnState('loginBtn', 'Authenticating...')
    let inp_email = $('#inp_email').val();
    let inp_password = $('#inp_password').val();

    let params = "email="+inp_email+"&password="+inp_password;
    dbaction("/action/login.php",params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
        if(feed.includes("ucce")){
            const loginBtn = document.getElementById('loginBtn');
            if(loginBtn){
                loginBtn.innerHTML = 'OKAY';
                loginBtn.attributes[1].value = 'disabled';
            }
        }else{
            toggleBtnState('loginBtn', 'LOGIN')
        }
        
    })

}

function resend_otp(dest){
    let params = "dest="+dest;
    dbaction("/action/otp_resend",params, function (feed) {
        console.log(JSON.stringify(feed));
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "3");
    })

}

function confirm_2f(){
    let otp = $('#otp_').val();

    let params = "otp="+otp;
    dbaction("/action/otp_confirm",params, function (feed) {
        console.log(JSON.stringify(feed));
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");

    })

}
