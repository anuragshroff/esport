
        // Show/hide team name based on tournament type
        document.getElementById("tournamentType").addEventListener("change", function() {
            const teamNameGroup = document.getElementById("teamNameGroup");
            if (this.value === "Team") {
                teamNameGroup.classList.remove("hidden");
            } else {
                teamNameGroup.classList.add("hidden");
            }
        });

        // Proceed to payment section
        document.getElementById("goToPaymentBtn").addEventListener("click", function() {
            document.getElementById("paymentSection").classList.remove("hidden");
            // Smooth scroll to payment section
            document.getElementById("paymentSection").scrollIntoView({ behavior: 'smooth' });
        });

        // For demo, call confirmPayment after 5 seconds (simulate payment confirmation)
        setTimeout(confirmPayment, 5000);  // This is just for testing, remove in live system
        
        function confirmPayment() {
            document.getElementById("paymentConfirmation").classList.remove("hidden");
        }

        // Form submission with AJAX
        document.getElementById("registrationForm").addEventListener("submit", function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitBtn = document.getElementById("submitRegistration");
            submitBtn.innerHTML = "Processing... <span class='inline-block w-5 h-5 border-3 border-secondary border-t-secondary rounded-full animate-spin ml-2'></span>";
            submitBtn.disabled = true;
            
            const formData = new FormData(this);
            
            fetch("./php/send-email.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById("registrationMessage");
                
                if (data.success) {
                    document.getElementById("registrationForm").classList.add("hidden");
                    messageDiv.classList.remove("hidden");
                    messageDiv.innerHTML = `
                        <div class="p-6 bg-[rgba(0,255,0,0.1)] border border-success rounded">
                            <h2 class="text-xl mb-4"><i class="fas fa-check-circle text-success"></i> Registration Complete!</h2>
                            <p>${data.message}</p>
                            <p class="mt-2">Your confirmation code: <strong>${data.confirmationCode}</strong></p>
                        </div>
                    `;
                } else {
                    submitBtn.innerHTML = "Complete Registration <i class='fas fa-check ml-2'></i>";
                    submitBtn.disabled = false;
                    messageDiv.classList.remove("hidden");
                    messageDiv.innerHTML = `
                        <div class="p-6 bg-[rgba(255,0,0,0.1)] border border-red-500 rounded">
                            <h2 class="text-xl mb-4"><i class="fas fa-exclamation-triangle text-red-500"></i> Registration Failed</h2>
                            <p>${data.message}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById("submitRegistration").innerHTML = "Complete Registration <i class='fas fa-check ml-2'></i>";
                document.getElementById("submitRegistration").disabled = false;
                
                const messageDiv = document.getElementById("registrationMessage");
                messageDiv.classList.remove("hidden");
                messageDiv.innerHTML = `
                    <div class="p-6 bg-[rgba(255,0,0,0.1)] border border-red-500 rounded">
                        <h2 class="text-xl mb-4"><i class="fas fa-exclamation-triangle text-red-500"></i> Error</h2>
                        <p>There was a problem processing your registration. Please try again later.</p>
                    </div>
                `;
            });
        });
// Initialize Swiper
var swiper = new Swiper(".mySwiper", {
    loop: true,
    autoplay: {
        delay: 5000,
        disableOnInteraction: false,
    },
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    }
});
