// Login View
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

// Dashboard View
var newOrder = document.getElementById("newOrder");

function showNewOrder(){
    if(newOrder) newOrder.style.display = 'block';
}
function hideNewOrder(){
    if(newOrder) newOrder.style.display = 'none';
}

// Cost Calculator
function calculateTotal() {
    // Service Rates
    const RATE_WASH = 50;
    const RATE_DRY = 60;
    const RATE_FOLD = 35;
    const RATE_DETERGENT = 20;

    // Parse quantities
    var qtyColored = document.getElementById('qtyColored');
    var qtyWhite = document.getElementById('qtyWhite');
    
    // Validate elements
    if (!qtyColored || !qtyWhite) return;

    var valColored = parseInt(qtyColored.value) || 0;
    var valWhite = parseInt(qtyWhite.value) || 0;
    var totalLoads = valColored + valWhite;

    var costPerLoad = 0;

    // Check services
    var serviceWash = document.getElementById('serviceWash');
    var serviceDry = document.getElementById('serviceDry');
    var serviceFold = document.getElementById('serviceFold');
    var supplyDetergent = document.getElementById('supplyDetergent');

    if (serviceWash && serviceWash.checked) costPerLoad += RATE_WASH;
    if (serviceDry && serviceDry.checked) costPerLoad += RATE_DRY;
    if (serviceFold && serviceFold.checked) costPerLoad += RATE_FOLD;
    if (supplyDetergent && supplyDetergent.checked) costPerLoad += RATE_DETERGENT;

    // Total cost
    var totalPayment = totalLoads * costPerLoad;

    // Show price
    var priceDisplay = document.getElementById('price');
    if (priceDisplay) {
        priceDisplay.innerText = "₱" + totalPayment;
    }
}