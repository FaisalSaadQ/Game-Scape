document.addEventListener('DOMContentLoaded', function() {
    // Add event listener for the "Confirm & Pay" button
    document.querySelector('.checkout-btn').addEventListener('click', function(event) {
        event.preventDefault();  // Prevents the form from submitting immediately

        // Get product ID and quantity (adjust according to your logic)
        let productId = 1;  // Example: Hardcoded product ID for now
        let quantity = 1;   // Hardcoded quantity for now (you can modify based on user input)

        // Get form field values
        let name = document.getElementById('name').value;
        let address = document.getElementById('address').value;
        let city = document.getElementById('city').value;
        let zip = document.getElementById('zip').value;
        let country = document.getElementById('country').value;
        let cardName = document.getElementById('card-name').value;
        let cardNumber = document.getElementById('card-number').value;
        let exp = document.getElementById('exp').value;
        let cvv = document.getElementById('cvv').value;

        // Simple validation for empty fields
        if (!name || !address || !city || !zip || !country || !cardName || !cardNumber || !exp || !cvv) {
            alert("Please fill in all fields!");
            return;
        }

        // Validate card number (simple validation for 16 digits)
        if (!/^\d{16}$/.test(cardNumber)) {
            alert("Please enter a valid 16-digit card number.");
            return;
        }

        // Validate expiration date (MM/YY format)
        let expDateRegex = /^(0[1-9]|1[0-2])\/([0-9]{2})$/;
        if (!expDateRegex.test(exp)) {
            alert("Please enter a valid expiration date in MM/YY format.");
            return;
        }

        // Validate CVV (3 digits)
        if (!/^\d{3}$/.test(cvv)) {
            alert("Please enter a valid 3-digit CVV.");
            return;
        }

        // Prepare data to send to the backend (checkout.php)
        let formData = new FormData();
        formData.append("product_id", productId);
        formData.append("quantity", quantity);
        formData.append("name", name);
        formData.append("address", address);
        formData.append("city", city);
        formData.append("zip", zip);
        formData.append("country", country);
        formData.append("card_name", cardName);
        formData.append("card_number", cardNumber);
        formData.append("exp", exp);
        formData.append("cvv", cvv);

        // Log the data to the console to ensure it's being sent correctly
        console.log("Form data being sent:", {
            productId,
            quantity,
            name,
            address,
            city,
            zip,
            country,
            cardName,
            cardNumber,
            exp,
            cvv
        });

        // Send the data to checkout.php using Fetch API
        fetch('checkout.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Response from server:', data); // Log server response for debugging

            if (data.success) {
                alert("Payment Successful!");
                window.location.href = "/thank-you.html"; // Redirect after success
            } else {
                alert("Payment failed. Please try again.");
            }
        })
        .catch(error => {
            console.error("Error details: ", error);  // Log the error details in the console
            alert("An error occurred: " + error.message);  // Display a more detailed error message to the user
        });
    });
});
