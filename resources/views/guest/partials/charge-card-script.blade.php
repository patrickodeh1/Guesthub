@once
<script src="https://js.stripe.com/v3/"></script>
<script>
function initGuestChargeCard(type) {
    var wizard = document.getElementById(type + "-card-wizard");
    var payBtn = document.getElementById(type + "-pay-btn");
    var summaryList = document.getElementById(type + "-summary-list");
    var stepNumber = document.getElementById(type + "-step-number");
    var stepExpiry = document.getElementById(type + "-step-expiry");
    var stepCvc = document.getElementById(type + "-step-cvc");
    var stepPostal = document.getElementById(type + "-step-postal");
    var postalField = document.getElementById(type + "-payment-card-postal");
    if (!wizard || !payBtn || !stepNumber) return;
    var errorBox = document.getElementById(type + "-payment-error");
    var stripe, elements, cardNumber, cardExpiry, cardCvc, clientSecret = null;
    var cardEl = wizard.closest("[data-charge-card]");
    var intentUrl = cardEl.dataset.intentUrl;
    var confirmUrl = cardEl.dataset.confirmUrl;
    var payLabel = payBtn.textContent;
    var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : "";

    function showError(msg) {
        errorBox.textContent = msg;
        errorBox.classList.remove("hidden");
    }

    function addSummaryRow(label, brand) {
        var row = document.createElement("div");
        row.className = "flex items-center gap-2 text-xs font-semibold text-slate-500 mb-2";
        var icon = document.createElement("span");
        icon.textContent = "✓";
        icon.className = "text-emerald-600";
        var text = document.createElement("span");
        text.textContent = brand ? (label + " (" + brand + ")") : label;
        row.append(icon, text);
        summaryList.appendChild(row);
    }

    function showSuccess(msg) {
        cardEl.innerHTML = "";
        var content = document.createElement("div");
        content.className = "p-6 md:p-8 text-center";
        var check = document.createElement("div");
        check.className = "guest-big-check mx-auto";
        check.textContent = "✓";
        var heading = document.createElement("p");
        heading.className = "mt-4 text-base font-bold text-slate-950";
        heading.textContent = "Payment received";
        var message = document.createElement("p");
        message.className = "mt-2 text-sm leading-6 text-slate-600";
        message.textContent = msg || "Your payment has been received.";
        content.append(check, heading, message);
        cardEl.appendChild(content);
    }

    function makeStripeFieldAccessible(fieldId, label) {
        var attempts = 0;
        var timer = window.setInterval(function() {
            var field = document.querySelector('#' + fieldId + ' .__PrivateStripeElement-input');
            if (field) {
                field.setAttribute('aria-hidden', 'false');
                field.setAttribute('aria-label', label);
                field.setAttribute('autocomplete', 'off');
                window.clearInterval(timer);
                return;
            }
            attempts += 1;
            if (attempts >= 25) {
                window.clearInterval(timer);
            }
        }, 100);
    }

    fetch(intentUrl, {
        method: "POST",
        headers: { "Accept": "application/json", "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
        body: JSON.stringify({ type: type })
    })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.ok) { showError(data.error || "Unable to start payment."); return; }
            if (!data.client_secret) { cardEl.style.display = "none"; return; }
            clientSecret = data.client_secret;
            stripe = Stripe(data.publishable_key);
            elements = stripe.elements({ clientSecret: clientSecret });
            var stripeElementStyle = {
                base: {
                    color: "#0f172a",
                    fontSize: "16px",
                    fontFamily: "inherit",
                    lineHeight: "24px",
                                "::placeholder": { color: "#94a3b8" }
                },
                invalid: { color: "#dc2626" }
            };
            cardNumber = elements.create("cardNumber", {
                showIcon: true,
                disableLink: true,
                placeholder: "1234 5678 9012 3456",
                classes: { base: "guest-card-field-inner" },
                style: stripeElementStyle
            });
            cardExpiry = elements.create("cardExpiry", {
                placeholder: "MM / YY",
                classes: { base: "guest-card-field-inner" },
                style: stripeElementStyle
            });
            cardCvc = elements.create("cardCvc", {
                placeholder: "CVV",
                classes: { base: "guest-card-field-inner" },
                style: stripeElementStyle
            });
            cardNumber.mount("#" + type + "-payment-card-number");
            cardExpiry.mount("#" + type + "-payment-card-expiry");
            cardCvc.mount("#" + type + "-payment-card-cvc");
            makeStripeFieldAccessible(type + "-payment-card-number", "Card number");
            makeStripeFieldAccessible(type + "-payment-card-expiry", "Card expiry");
            makeStripeFieldAccessible(type + "-payment-card-cvc", "Card security code");

            var numberDone = false, expiryDone = false, cvcDone = false;

            cardNumber.on("change", function(event) {
                if (event.error) { showError(event.error.message); return; }
                errorBox.classList.add("hidden");
                if (event.complete && !numberDone) {
                    numberDone = true;
                                        stepExpiry.classList.remove("hidden");
                    cardExpiry.focus();
                }
            });
            cardExpiry.on("change", function(event) {
                if (event.error) { showError(event.error.message); return; }
                errorBox.classList.add("hidden");
                if (event.complete && !expiryDone) {
                    expiryDone = true;
                                        stepCvc.classList.remove("hidden");
                    cardCvc.focus();
                }
            });
            cardCvc.on("change", function(event) {
                if (event.error) { showError(event.error.message); return; }
                errorBox.classList.add("hidden");
                if (event.complete && !cvcDone) {
                    cvcDone = true;
                                        stepPostal.classList.remove("hidden");
                    payBtn.classList.remove("hidden");
                    payBtn.disabled = false;
                    postalField.focus();
                }
            });
        })
        .catch(function() { showError("Network error. Please try again."); });

    payBtn.addEventListener("click", function() {
        if (!stripe || !elements || !cardNumber) return;
        payBtn.disabled = true;
        payBtn.textContent = "Processing…";
        errorBox.classList.add("hidden");

        stripe.confirmCardPayment(clientSecret || "", {
            payment_method: {
                card: cardNumber,
                billing_details: {
                    address: {
                        postal_code: (postalField.value || '').trim()
                    }
                }
            }
        })
            .then(function(result) {
                if (result.error) {
                    showError(result.error.message || "Payment failed. Please try again.");
                    payBtn.disabled = false;
                    payBtn.textContent = payLabel;
                    return;
                }
                return fetch(confirmUrl, {
                    method: "POST",
                    headers: { "Accept": "application/json", "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
                    body: JSON.stringify({ payment_intent_id: result.paymentIntent.id })
                })
                    .then(function(r) { return r.json(); })
                    .then(function(confirmData) {
                        if (confirmData.ok) {
                            if (type === "late_checkout") {
                                showSuccess(confirmData.message || "Your late checkout payment has been received.");
                                window.setTimeout(function() {
                                    cardEl.remove();
                                }, 15000);
                            } else {
                                showSuccess(confirmData.message || "Your payment has been received.");
                            }
                        } else {
                            showError(confirmData.error || "Payment could not be confirmed. Please contact us.");
                            payBtn.disabled = false;
                        }
                    });
            })
            .catch(function() {
                showError("Network error confirming payment. Please try again.");
                payBtn.disabled = false;
            });
    });
}
</script>
@endonce
