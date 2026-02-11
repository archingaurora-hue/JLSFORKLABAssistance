document.addEventListener('DOMContentLoaded', function() {
    
    // Checkboxes
    const checkWash = document.getElementById('checkWash');
    const checkDry = document.getElementById('checkDry');
    const checkFold = document.getElementById('checkFold');

    // Supplies
    const suppliesSection = document.getElementById('suppliesSection'); 
    const supplyDetergent = document.getElementById('supplyDetergent');
    const supplySoftener = document.getElementById('supplySoftener');
    const suppliesHeader = document.getElementById('suppliesHeader');

    // Inputs
    const standardInputs = document.getElementById('standardInputs');
    const foldOnlyInput = document.getElementById('foldOnlyInput');
    const qtyColored = document.getElementById('qtyColored');
    const qtyWhite = document.getElementById('qtyWhite');
    const qtyFold = document.getElementById('qtyFold');

    // UI Elements
    const errorWetClothes = document.getElementById('errorWetClothes');
    const btnPlaceOrder = document.getElementById('btnPlaceOrder');
    const totalPriceDisplay = document.getElementById('totalPrice');

    const elements = [checkWash, checkDry, checkFold, supplyDetergent, supplySoftener, qtyColored, qtyWhite, qtyFold];
    elements.forEach(el => {
        if(el) {
            el.addEventListener('change', updateOrderState);
            el.addEventListener('input', updateOrderState);
        }
    });

    function updateOrderState() {
        const isWash = checkWash.checked;
        const isDry = checkDry.checked;
        const isFold = checkFold.checked;

        // 1. Logic: Supplies only active if Wash is checked
        if (isWash) {
            supplyDetergent.disabled = false;
            supplySoftener.disabled = false;
            
            // Explicitly enable the container to allow clicks
            if (suppliesSection) {
                suppliesSection.style.pointerEvents = 'auto';
                suppliesSection.classList.remove('opacity-50');
            }

            if (suppliesHeader) {
                suppliesHeader.classList.remove('text-muted');
                suppliesHeader.classList.add('text-dark');
            }
        } else {
            supplyDetergent.disabled = true;
            supplySoftener.disabled = true;
            supplyDetergent.checked = false;
            supplySoftener.checked = false;

            // Lock the container again
            if (suppliesSection) {
                suppliesSection.style.pointerEvents = 'none';
                suppliesSection.classList.add('opacity-50');
            }

            if (suppliesHeader) {
                suppliesHeader.classList.add('text-muted');
                suppliesHeader.classList.remove('text-dark');
            }
        }

        // 2. Logic: "Wet clothes cannot be folded" (Wash + Fold, No Dry)
        let isInvalid = false;
        if (isWash && isFold && !isDry) {
            errorWetClothes.classList.remove('d-none');
            isInvalid = true;
        } else {
            errorWetClothes.classList.add('d-none');
        }

        // 3. Logic: Input switching (Fold Only vs Standard)
        const isFoldOnly = isFold && !isWash && !isDry;

        if (isFoldOnly) {
            standardInputs.classList.add('d-none');
            foldOnlyInput.classList.remove('d-none');
        } else {
            standardInputs.classList.remove('d-none');
            foldOnlyInput.classList.add('d-none');
        }

        // 4. Calculate Total
        let totalLoadCount = 0;
        
        if (isFoldOnly) {
            totalLoadCount = parseInt(qtyFold.value) || 0;
        } else {
            totalLoadCount = (parseInt(qtyColored.value) || 0) + (parseInt(qtyWhite.value) || 0);
        }

        let costPerLoad = 0;
        if (isWash) costPerLoad += 50;
        if (isDry) costPerLoad += 60;
        if (isFold) costPerLoad += 35;
        
        if (isWash) {
            if (supplyDetergent.checked) costPerLoad += 20;
            if (supplySoftener.checked) costPerLoad += 10;
        }

        const grandTotal = totalLoadCount * costPerLoad;
        totalPriceDisplay.innerText = "₱" + grandTotal.toFixed(2);

        // 5. Button State
        if (isInvalid || grandTotal === 0 || (!isWash && !isDry && !isFold)) {
            btnPlaceOrder.disabled = true;
        } else {
            btnPlaceOrder.disabled = false;
        }
    }

    updateOrderState();
});