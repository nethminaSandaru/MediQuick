document.addEventListener("DOMContentLoaded", function () {

    // Phone number validation
    const phoneInputs = document.querySelectorAll(
        'input[name="phone"]'
    );

    phoneInputs.forEach(function (input) {

        input.addEventListener("input", function () {

            this.value = this.value.replace(
                /[^0-9+]/g,
                ""
            );

        });

    });


    // Password strength
    const password = document.querySelector(
        'input[name="password"]'
    );

    const confirmPassword = document.querySelector(
        'input[name="confirm_password"]'
    );


    if (password) {

        password.addEventListener("input", function () {

            const value = this.value;

            let strength = 0;

            if (value.length >= 8) strength++;

            if (/[A-Z]/.test(value)) strength++;

            if (/[a-z]/.test(value)) strength++;

            if (/[0-9]/.test(value)) strength++;

            if (/[^A-Za-z0-9]/.test(value)) strength++;


            const indicator =
                document.getElementById("password-strength");


            if (indicator) {

                if (strength <= 2) {

                    indicator.textContent =
                        "Weak password";

                } else if (strength <= 4) {

                    indicator.textContent =
                        "Medium password";

                } else {

                    indicator.textContent =
                        "Strong password";

                }

            }

        });

    }


    // Confirm password
    if (confirmPassword && password) {

        confirmPassword.addEventListener("input", function () {

            if (this.value !== password.value) {

                this.setCustomValidity(
                    "Passwords do not match."
                );

            } else {

                this.setCustomValidity("");

            }

        });

    }

});