// Product Detail JavaScript
let basePrice = {};
let productId = null;

// Initialize base price from server data
function initializeBasePrice(priceData) {
    console.log("Initializing base price:", priceData);
    basePrice = priceData;
}

// Initialize product ID
function initializeProductId(id) {
    console.log("Initializing product ID:", id);
    productId = id;
}

// Thumbnail gallery functionality
function showImage(thumbnail) {
    const mainImage = document.getElementById("main-image");
    mainImage.src = thumbnail.src;
    document.querySelectorAll(".custom-scrollbar img").forEach((thumb) => {
        thumb.classList.remove("thumbnail-active");
    });
    thumbnail.classList.add("thumbnail-active");
}

// Update price display
function updatePrices(prices) {
    document.getElementById("total-price-gbp").textContent =
        prices.gbp.toFixed(2);
    document.getElementById("total-price-usd").textContent =
        prices.usd.toFixed(2);
    document.getElementById("total-price-vnd").textContent = Math.round(
        prices.vnd
    ).toLocaleString("vi-VN");
}

// Find matching variant based on selected attributes
async function findMatchingVariant() {
    const selectedValues = {};
    const selects = document.querySelectorAll(".attribute-select");

    selects.forEach((select) => {
        // Lấy tên attribute từ label thay vì từ name
        const label = select.previousElementSibling;
        const name = label ? label.textContent.trim() : select.name;
        selectedValues[name] = select.value;

        console.log("Selected attribute:", {
            name: name,
            value: select.value,
            label: label ? label.textContent.trim() : "No label",
        });
    });

    // Debug: Log selected values
    console.log("Selected values:", selectedValues);

    const allSelected = Object.values(selectedValues).every(
        (value) => value !== ""
    );
    const skuElement = document.getElementById("selected-sku");

    if (!allSelected) {
        skuElement.textContent = "-";
        updatePrices(basePrice);
        return null;
    }

    try {
        const requestData = {
            product_id: productId,
            attributes: selectedValues,
        };

        console.log("Sending request:", requestData);

        const response = await fetch("/api/variant-info", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify(requestData),
        });

        const data = await response.json();
        console.log("Response:", data);

        if (data.success) {
            skuElement.textContent = data.variant.sku || "-";
            return data.variant;
        } else {
            skuElement.textContent = "No matching variant found";
            updatePrices(basePrice);
            return null;
        }
    } catch (error) {
        console.error("Error fetching variant info:", error);
        console.error("Error details:", {
            message: error.message,
            stack: error.stack,
            requestData: requestData,
        });
        skuElement.textContent = "Error loading variant";
        updatePrices(basePrice);
        return null;
    }
}

// Update shipping price based on selected variant and shipping method
async function updateShippingPrice() {
    const currentVariant = await findMatchingVariant();
    const shippingMethod = document.getElementById("shipping-method").value;

    if (!currentVariant) {
        alert("Please select all product options");
        document.getElementById("shipping-method").value = "";
        updatePrices(basePrice);
        return;
    }

    if (!shippingMethod) {
        updatePrices(basePrice);
        return;
    }

    try {
        const response = await fetch("/api/variant-info", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({
                product_id: productId,
                attributes: getSelectedAttributes(),
                shipping_method: shippingMethod,
            }),
        });

        const data = await response.json();

        if (data.success && data.variant.shipping_price) {
            const shippingPrice = data.variant.shipping_price;
            // Sử dụng giá mặc định từ bảng ShippingPrice
            const total = {
                gbp: parseFloat(shippingPrice.price_gbp || 0),
                usd: parseFloat(shippingPrice.price_usd || 0),
                vnd: parseFloat(shippingPrice.price_vnd || 0),
            };
            updatePrices(total);
        } else {
            // Nếu không có shipping price, hiển thị giá sản phẩm cơ bản
            const variant = data.variant;
            if (variant) {
                const total = {
                    gbp: parseFloat(variant.price_gbp || 0),
                    usd: parseFloat(variant.price_usd || 0),
                    vnd: parseFloat(variant.price_vnd || 0),
                };
                updatePrices(total);
            } else {
                updatePrices(basePrice);
            }
        }
    } catch (error) {
        console.error("Error fetching shipping price:", error);
        console.error("Shipping price error details:", {
            message: error.message,
            stack: error.stack,
            currentVariant: currentVariant,
            shippingMethod: shippingMethod,
        });
        updatePrices(basePrice);
    }
}

// Helper function to get selected attributes
function getSelectedAttributes() {
    const selectedValues = {};
    const selects = document.querySelectorAll(".attribute-select");

    selects.forEach((select) => {
        const name = select.name
            .replace(/-/g, " ")
            .replace(/\b\w/g, (l) => l.toUpperCase());
        selectedValues[name] = select.value;
    });

    return selectedValues;
}

// Initialize event listeners
document.addEventListener("DOMContentLoaded", () => {
    console.log("DOM loaded, initializing event listeners");

    document.querySelectorAll(".attribute-select").forEach((select) => {
        select.addEventListener("change", async () => {
            console.log("Attribute select changed:", select.name);
            await findMatchingVariant();
            document.getElementById("shipping-method").value = "";
            updatePrices(basePrice);
        });
    });

    // Initialize first load
    console.log("Running initial findMatchingVariant");
    findMatchingVariant();
    updatePrices(basePrice);
});
