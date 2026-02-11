//login.php
var register = document.getElementById("register");
var login = document.getElementById("login");
var passRecovery = document.getElementById("passRecovery");

function showLogin() {
    if(register) register.style.display = 'none';
    if(login) login.style.display = 'block';
    if(passRecovery) passRecovery.style.display = 'none';
}
function showRegister() {
    if(login) login.style.display = 'none';
    if(register) register.style.display = 'block';
    if(passRecovery) passRecovery.style.display = 'none';
}
function showPassRecovery() {
    if(register) register.style.display = 'none';
    if(login) login.style.display = 'none';
    if(passRecovery) passRecovery.style.display = 'block';
}

//dashboard.php
var newOrder = document.getElementById("newOrder");

function showNewOrder(){
    if(newOrder) newOrder.style.display = 'block';
}
function hideNewOrder(){
    if(newOrder) newOrder.style.display = 'none';
}

// COST CALCULATION LOGIC
function calculateTotal() {
    // Rates from UI
    const RATE_WASH = 50;
    const RATE_DRY = 60;
    const RATE_FOLD = 35;
    const RATE_DETERGENT = 20;

    // Get Quantities (Default to 0 if empty)
    var qtyColored = document.getElementById('qtyColored');
    var qtyWhite = document.getElementById('qtyWhite');
    
    // Safety check if elements exist
    if (!qtyColored || !qtyWhite) return;

    var valColored = parseInt(qtyColored.value) || 0;
    var valWhite = parseInt(qtyWhite.value) || 0;
    var totalLoads = valColored + valWhite;

    var costPerLoad = 0;

    // Check selected services
    var serviceWash = document.getElementById('serviceWash');
    var serviceDry = document.getElementById('serviceDry');
    var serviceFold = document.getElementById('serviceFold');
    var supplyDetergent = document.getElementById('supplyDetergent');

    if (serviceWash && serviceWash.checked) costPerLoad += RATE_WASH;
    if (serviceDry && serviceDry.checked) costPerLoad += RATE_DRY;
    if (serviceFold && serviceFold.checked) costPerLoad += RATE_FOLD;
    if (supplyDetergent && supplyDetergent.checked) costPerLoad += RATE_DETERGENT;

    // Calculate Total
    var totalPayment = totalLoads * costPerLoad;

    // Update Display
    var priceDisplay = document.getElementById('price');
    if (priceDisplay) {
        priceDisplay.innerText = "₱" + totalPayment;
    }
}