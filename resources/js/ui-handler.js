// Xử lý UI và DOM
export class UIHandler {
    constructor(variantManager) {
        this.variantManager = variantManager;
    }

    updateVariantsList() {
        const variantsList = document.querySelector(
            ".inline-flex.flex-wrap.gap-2"
        );
        variantsList.innerHTML = "";

        this.variantManager.variants.forEach((variant) => {
            variant.options.forEach((option) => {
                const button = this.createOptionButton(option);
                variantsList.appendChild(button);
            });
        });

        this.updateVariantTable();
    }

    createOptionButton(option) {
        const button = document.createElement("button");
        button.type = "button";
        button.className =
            "inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-800 border border-gray-300 rounded-md hover:text-white hover:bg-blue-700 dark:bg-white/[0.03] dark:text-gray-200 dark:border-gray-700 dark:hover:bg-white/[0.03]";
        button.textContent = option;
        button.addEventListener("click", () =>
            this.handleOptionClick(button, option)
        );
        return button;
    }

    updateVariantTable() {
        const tableBody = document.querySelector('[x-data="dataTableThree()"]');
        if (!tableBody) return;

        const existingRows = tableBody.querySelectorAll(".variant-row");
        existingRows.forEach((row) => row.remove());

        this.variantManager.variantCombinations.forEach((combination) => {
            const row = this.createVariantRow(combination);
            tableBody.appendChild(row);
        });
    }

    createVariantRow(combination) {
        const row = document.createElement("div");
        row.className =
            "variant-row grid grid-cols-12 border-t border-gray-100 dark:border-gray-800";
        // Thêm nội dung row
        return row;
    }
}
