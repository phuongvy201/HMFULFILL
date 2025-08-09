// Product Detail Dynamic Updates
document.addEventListener("DOMContentLoaded", function () {
    initializeDynamicUpdates();
});

function initializeDynamicUpdates() {
    const attributeSelects = document.querySelectorAll(".attribute-select");
    const shippingMethodSelect = document.querySelector(
        ".shipping-method-select"
    );
    const productId = window.productId; // Sẽ được set từ view

    // Add event listeners to attribute selects
    attributeSelects.forEach((select) => {
        select.addEventListener("change", () => {
            updateVariantInfo();
        });
    });

    // Add event listener to shipping method select
    if (shippingMethodSelect) {
        shippingMethodSelect.addEventListener("change", () => {
            updateVariantInfo();
        });
    }

    // Function to update variant info
    function updateVariantInfo() {
        const selectedAttributes = {};

        // Collect all selected attributes
        attributeSelects.forEach((select) => {
            const attributeName = select.getAttribute("data-attribute-name");
            const selectedValue = select.value;
            if (selectedValue) {
                selectedAttributes[attributeName] = selectedValue;
            }
        });

        // Get shipping method
        const shippingMethod = shippingMethodSelect
            ? shippingMethodSelect.value
            : "";

        // Show loading state
        showLoadingState();

        // Make API call
        fetch("/api/variant-info", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({
                product_id: productId,
                attributes: selectedAttributes,
                shipping_method: shippingMethod,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    updateDisplay(data.variant);
                } else {
                    // Reset to base prices if no variant found
                    resetToBasePrices();
                }
            })
            .catch((error) => {
                console.error("Error updating variant info:", error);
                resetToBasePrices();
            })
            .finally(() => {
                hideLoadingState();
            });
    }

    function updateDisplay(variant) {
        // Update SKU
        const skuElement = document.querySelector(".sku-display");
        if (skuElement) {
            skuElement.textContent = variant.sku;
        }

        // Update prices
        const priceElement = document.querySelector(".price-display");
        if (priceElement && variant.shipping_price) {
            const prices = variant.shipping_price;
            priceElement.innerHTML = `
                <span>USD $${formatPrice(prices.price_usd)}</span> |
                <span>GBP £${formatPrice(prices.price_gbp)}</span> |
                <span>VND ₫${formatPrice(prices.price_vnd, 0)}</span>
            `;
        } else if (priceElement) {
            // Use variant base prices
            priceElement.innerHTML = `
                <span>USD $${formatPrice(variant.price_usd)}</span> |
                <span>GBP £${formatPrice(variant.price_gbp)}</span> |
                <span>VND ₫${formatPrice(variant.price_vnd, 0)}</span>
            `;
        }
    }

    function resetToBasePrices() {
        // Reset to base product prices
        const skuElement = document.querySelector(".sku-display");
        if (skuElement) {
            skuElement.textContent = "-";
        }

        // Keep original prices from server
        // They will be displayed by default
    }

    function showLoadingState() {
        const skuElement = document.querySelector(".sku-display");
        const priceElement = document.querySelector(".price-display");

        if (skuElement) skuElement.textContent = "Loading...";
        if (priceElement)
            priceElement.innerHTML =
                '<span class="text-gray-400">Loading prices...</span>';
    }

    function hideLoadingState() {
        // Loading state will be replaced by actual data
    }

    function formatPrice(price, decimals = 2) {
        return parseFloat(price || 0).toLocaleString("en-US", {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        });
    }
}
