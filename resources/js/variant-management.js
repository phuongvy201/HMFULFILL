// Quản lý variants và combinations
export class VariantManager {
    constructor() {
        this.variants = [];
        this.variantCombinations = [];
        this.selectedOptions = [];
    }

    generateCombinations() {
        if (this.variants.length === 0) return [];

        const optionsArray = this.variants.map((v) => v.options);

        function combine(current, arrays) {
            if (arrays.length === 0) return [current];

            const results = [];
            const currentArray = arrays[0];
            const remainingArrays = arrays.slice(1);

            for (const item of currentArray) {
                results.push(...combine([...current, item], remainingArrays));
            }

            return results;
        }

        return combine([], optionsArray);
    }

    addVariant(variant) {
        this.variants.push(variant);
        this.variantCombinations = this.generateCombinations();
    }

    removeVariant(variantId) {
        this.variants = this.variants.filter((v) => v.id !== variantId);
        this.variantCombinations = this.generateCombinations();
    }
}
