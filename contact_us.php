<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact Us</title>
    <link rel="stylesheet" href="css/header-footer.css" />
    <link rel="stylesheet" href="css/contact-us.css" />
    <style>
        .error-message {
            color: red;
            font-size: 0.9em;
            margin-top: 4px;
            margin-left: 100px;
            margin-right: 100px;
            display: none;
        }
    </style>
</head>

<body>

    <?php include "includes/header.php"; ?>

    <!-- Contact Us Form -->
    <div class="container">
        <h2>Contact Us</h2>
        <form id="contactForm" novalidate>
            <input type="text" id="firstName" placeholder="First Name *" required />
            <div id="error-firstName" class="error-message"></div>

            <input type="text" id="lastName" placeholder="Last Name *" required />
            <div id="error-lastName" class="error-message"></div>

            <input type="tel" id="contactNo" placeholder="Contact No *" required />
            <div id="error-contactNo" class="error-message"></div>

            <input type="email" id="email" placeholder="Email *" required />
            <div id="error-email" class="error-message"></div>

            <input type="text" id="orderNo" placeholder="Order No" />
            <input type="text" id="subject" placeholder="Subject" />
            <textarea id="description" placeholder="Description"></textarea>

            <button type="submit">Submit</button>
        </form>

        <h3 style="color: white;">Find us on the map:</h3>
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2786.481513475672!2d50.17726787077704!3d26.37141866745545!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e49e58f5aba28a1%3A0xa7fd1dabd67603!2sAl%20Abdulkarim%20Tower!5e0!3m2!1sen!2ssa!4v1746361206965!5m2!1sen!2ssa"
            width="450"
            height="225"
            style="border:0;"
            allowfullscreen
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>

    <?php include "includes/footer.html"; ?>

    <!-- Popup -->
    <div id="popup" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%);
    background-color: #050807; padding:20px; border-radius:10px; box-shadow:0 0 10px #000; z-index:1000; color: white; text-align: center;">
        <h3>Feedback Sent!</h3>
        <p>Your feedback is successfully sent</p>
        <button onclick="document.getElementById('popup').style.display='none'"
            style="padding: 8px 16px; background-color: white; color: #3DBDA7; border: none; border-radius: 5px; cursor: pointer;">
            OK
        </button>
    </div>

    <!-- JS Validation -->
    <script>
        document.getElementById('contactForm').addEventListener('submit', function(event) {
            event.preventDefault();

            // Get field values
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const contactNo = document.getElementById('contactNo').value.trim();
            const email = document.getElementById('email').value.trim();

            // Get error containers
            const errorFirstName = document.getElementById('error-firstName');
            const errorLastName = document.getElementById('error-lastName');
            const errorContactNo = document.getElementById('error-contactNo');
            const errorEmail = document.getElementById('error-email');

            // Regex patterns
            const nameRegex = /^[A-Za-z\s\-']+$/;
            const phoneRegex = /^05\d{8}$/;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            // Reset all error messages
            [errorFirstName, errorLastName, errorContactNo, errorEmail].forEach(el => {
                el.innerText = '';
                el.style.display = 'none';
            });

            let isValid = true;

            // First name validation
            if (!firstName) {
                errorFirstName.innerText = 'First name is required.';
                errorFirstName.style.display = 'block';
                isValid = false;
            } else if (!nameRegex.test(firstName)) {
                errorFirstName.innerText = 'Only letters allowed in first name.';
                errorFirstName.style.display = 'block';
                isValid = false;
            }

            // Last name validation
            if (!lastName) {
                errorLastName.innerText = 'Last name is required.';
                errorLastName.style.display = 'block';
                isValid = false;
            } else if (!nameRegex.test(lastName)) {
                errorLastName.innerText = 'Only letters allowed in last name.';
                errorLastName.style.display = 'block';
                isValid = false;
            }

            // Phone validation
            if (!contactNo) {
                errorContactNo.innerText = 'Contact number is required.';
                errorContactNo.style.display = 'block';
                isValid = false;
            } else if (!phoneRegex.test(contactNo)) {
                errorContactNo.innerText = 'Phone must start with 05 and be 10 digits.';
                errorContactNo.style.display = 'block';
                isValid = false;
            }

            // Email validation
            if (!email) {
                errorEmail.innerText = 'Email is required.';
                errorEmail.style.display = 'block';
                isValid = false;
            } else if (!emailRegex.test(email)) {
                errorEmail.innerText = 'Invalid email format.';
                errorEmail.style.display = 'block';
                isValid = false;
            }

            // If valid, show success popup
            if (isValid) {
                this.reset();
                document.getElementById('popup').style.display = 'block';
            }
        });
    </script>

</body>

</html>