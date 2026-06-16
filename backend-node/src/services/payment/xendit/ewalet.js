const XenditPaymentRequest = require('./XenditPaymentRequest');
const paymentService = new XenditPaymentRequest('xnd_development_XXXXX');

async function handleCheckout() {
    const payload = {
        reference_id: "TRX-ERP-TOMPAK-001", // Contoh ID dari ERP Anda
        amount: 50000,
        payment_method: {
            type: "EWALLET",
            reusability: "ONE_TIME_USE",
            ewallet: {
                channelCode: "SHOPEEPAY",
                channelProperties: {
                    successReturnUrl: "https://your-app.com/payment-success"
                }
            }
        },
        customer: {
            referenceId: "USER-001",
            givenNames: "Kaindra",
            email: "kaindra@example.com"
        },
        metadata: {
            project: "ERP Tompak",
            module: "Tournament Fee"
        }
    };

    try {
        const result = await paymentService.create(payload);
        // Redirect user ke result.actions[0].url (untuk ShopeePay/QRIS)
        logger.info("Payment URL:", result.actions[0].url);
    } catch (err) {
        logger.error("Gagal membuat pembayaran:", err);
    }
}