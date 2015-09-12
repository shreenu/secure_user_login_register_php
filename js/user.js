$(function(){
    
    $.validator.addMethod("alphnum", function(value, element) {
        return this.optional(element) || /^[A-Za-z0-9]+$/i.test(value);
    }, "Username must contain only letters, numbers.");
    
    $("#login-form").validate({
        rules: {
            password: {
             required:true,   
            },
            email: {
              required: true,
              email: true,
            }
        },
         messages: {
            password: "Please Enter password",
            email: {
              required: "Please Enter Email your name",
              email: "Please enter valid email"
            }
        },
        submitHandler: function(){
            // Create a new element input, this will be our hashed password field. 
            var p = document.createElement("input");

            // Add the new element to our form. 
            form = document.getElementById("login-form");
            form.appendChild(p);
            p.name = "p";
            p.type = "hidden";
            p.value = CryptoJS.SHA512(form.password.value);

            // Make sure the plaintext password doesn't get sent. 
            form.password.value = "";
            return true;
        },
    });
    
    
    $("#register-form").validate({
        rules: {
            password: {
                required:true,
                minlength: 8,
            },
            confpassword:{
                minlength: 8,
                equalTo : "#password",
            },
            email: {
                required: true,
                email: true,
            },
            username:{
                alphnum: true,
                required: true,
            },
        },
         messages: {
            password: {
                required: "Please enter password",
            },
            email: {
                required: "Please enter Email",
                email: "Please enter valid Email",
            },
            confpassword:{
                equalTo: "please give same password",
            },
            username:{
                alphnum: "Username is not valid",
                required: "Please enter username",
            }
        },
        submitHandler: function(){            
            // Create a new element input, this will be our hashed password field. 
            var p = document.createElement("input");

            // Add the new element to our form. 
            form = document.getElementById("register-form");
            form.appendChild(p);
            p.name = "p";
            p.type = "hidden";
            p.value = CryptoJS.SHA512(form.password.value);

            // Make sure the plaintext password doesn't get sent. 
            form.password.value = "";
            form.confpassword.value = "";
            return true;
        },
    });
});

