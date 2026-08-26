@once
<script src="https://js.stripe.com/v3/"></script>
<script>
function initGuestChargeCard(type) {
    var payBtn = document.getElementById(type + "-pay-btn");
    var elementDiv = document.getElementById(type + "-payment-element");
    if (!payBtn || !elementDiv) return;
    var errorBox = document.getElementById(type + "-payment-error");
    var stripe, elements;
    var cardEl = elementDiv.closest("[data-charge-card]");
    var intentUrl = cardEl.dataset.intentUrl;
    var confirmUrl = cardEl.dataset.confirmUrl;
    var payLabel = payBtn.textContent;
    var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : "";

    function showError(msg) {
        errorBox.textContent = msg;
        errorBox.classList.remove("hidden");
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
            stripe = Stripe(data.publishable_key);
            elements = stripe.elements({ clientSecret: data.client_secret });
            elements.create("payment").mount("#" + type + "-payment-element");
            payBtn.disabled = false;
        })
        .catch(function() { showError("Network error. Please try again."); });

    payBtn.addEventListener("click", function() {
        if (!stripe || !elements) return;
        payBtn.disabled = true;
        payBtn.textContent = "Processing…";
        errorBox.classList.add("hidden");

        stripe.confirmPayment({ elements: elements, redirect: "if_required" })
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
                            window.location.reload();
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
