// composables/useMidtrans.ts
import { ref } from 'vue';

interface PaymentData {
    amount: number;
    customer: {
        name: string;
        email: string;
        phone: string;
    };
    items?: Array<{
        id: string;
        price: number;
        quantity: number;
        name: string;
    }>;
}

interface PaymentCallbacks {
    onSuccess?: (result: any) => void;
    onPending?: (result: any) => void;
    onError?: (result: any) => void;
    onClose?: () => void;
}

export function useMidtrans() {
    const isLoading = ref(false);
    const error = ref<string | null>(null);

    // Load Snap.js script
    const loadSnapScript = (): Promise<void> => {
        return new Promise((resolve, reject) => {
            if (window.snap) {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
            script.setAttribute('data-client-key', import.meta.env.VITE_MIDTRANS_CLIENT_KEY || '');
            
            script.onload = () => resolve();
            script.onerror = () => reject(new Error('Failed to load Snap.js'));
            
            document.head.appendChild(script);
        });
    };

    // Create payment and show Snap popup
    const createPayment = async (paymentData: PaymentData, callbacks: PaymentCallbacks = {}) => {
        try {
            isLoading.value = true;
            error.value = null;

            // Get CSRF token
            const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                throw new Error('CSRF token not found');
            }

            // Create payment token
            const response = await fetch('/api/payments/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify(paymentData),
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Network error' }));
                throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Failed to create payment');
            }

            // Load Snap.js if not already loaded
            await loadSnapScript();

            // Show Snap popup
            window.snap.pay(result.data.snap_token, {
                onSuccess: (result: any) => {
                    console.log('Payment success:', result);
                    callbacks.onSuccess?.(result);
                },
                onPending: (result: any) => {
                    console.log('Payment pending:', result);
                    callbacks.onPending?.(result);
                },
                onError: (result: any) => {
                    console.error('Payment error:', result);
                    callbacks.onError?.(result);
                },
                onClose: () => {
                    console.log('Payment popup closed');
                    callbacks.onClose?.();
                },
            });

            return result.data;

        } catch (err) {
            const errorMessage = err instanceof Error ? err.message : 'Payment failed';
            error.value = errorMessage;
            console.error('Payment error:', err);
            throw err;
        } finally {
            isLoading.value = false;
        }
    };

    return {
        isLoading,
        error,
        createPayment,
        loadSnapScript,
    };
}

// Type declarations for Snap.js
declare global {
    interface Window {
        snap: {
            pay: (token: string, callbacks: PaymentCallbacks) => void;
        };
    }
}