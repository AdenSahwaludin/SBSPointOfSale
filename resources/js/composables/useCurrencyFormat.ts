/**
 * Currency Formatting Composable
 * Handles Rupiah formatting with proper separators
 */

export function useCurrencyFormat() {
    /**
     * Format number as Rupiah currency (with dots as thousands separator)
     * @param amount - Amount in Rupiah
     * @returns Formatted string like "Rp 1.234.567"
     */
    function formatCurrency(amount: number | string): string {
        const num = typeof amount === 'string' ? parseFloat(amount) || 0 : amount;

        if (isNaN(num)) return 'Rp 0';

        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(num);
    }

    /**
     * Parse input value to number (remove non-numeric characters)
     * Handles both formatted and raw numbers
     * @param value - Input value (can be "1.234.567" or "1234567")
     * @returns Number without formatting
     */
    function parseCurrency(value: string): number {
        if (!value) return 0;
        // Remove all non-numeric characters except decimal point
        const cleaned = value.replace(/[^\d,]/g, '').replace(',', '.');
        return parseInt(cleaned, 10) || 0;
    }

    /**
     * Format number for display with thousands separator (dots)
     * @param amount - Amount in Rupiah (no formatting)
     * @returns Formatted string like "1.234.567"
     */
    function formatNumber(amount: number | string): string {
        const num = typeof amount === 'string' ? parseFloat(amount) || 0 : amount;

        if (isNaN(num)) return '0';

        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    /**
     * Parse number with dots separator back to plain number
     * @param value - Formatted string like "1.234.567"
     * @returns Number without formatting
     */
    function parseNumber(value: string): number {
        if (!value) return 0;
        return parseInt(value.replace(/\./g, ''), 10) || 0;
    }

    return {
        formatCurrency,
        parseCurrency,
        formatNumber,
        parseNumber,
    };
}
