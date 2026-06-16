const { Xendit } = require('xendit-node');

class XenditPaymentRequest {
    constructor(secretKey) {
        if (!secretKey) throw new Error("Xendit Secret Key is required");
        this.xenditClient = new Xendit({ secretKey });
    }

    /**
     * Internal helper untuk validasi data dasar
     */
    _validateBaseData(referenceId, amount) {
        if (!referenceId) throw new Error("reference_id is required");
        if (!amount || amount <= 0) throw new Error("Valid amount is required");
    }

    /**
     * Method Utama: Membuat Payment Request (Generic Engine)
     */
    async create(payloadData) {
        try {
            this._validateBaseData(payloadData.reference_id, payloadData.amount);
            
            logger.info(`[XENDIT_START] Creating Payment Request | Ref: ${payloadData.reference_id}`);

            // SDK v3 menggunakan camelCase untuk properti di dalam data
            const response = await this.xenditClient.PaymentRequest.createPaymentRequest({
                data: {
                    referenceId: payloadData.reference_id,
                    amount: payloadData.amount,
                    currency: payloadData.currency || 'IDR',
                    paymentMethod: payloadData.payment_method,
                    customer: payloadData.customer || null,
                    metadata: payloadData.metadata || {}
                }
            });

            logger.info(`[XENDIT_SUCCESS] ID: ${response.id} | Status: ${response.status}`);
            return response;

        } catch (error) {
            this._handleError('CREATE_PAYMENT', error);
        }
    }

    /**
     * Specialized Method: Membuat QRIS
     */
    async createQris(referenceId, amount, metadata = {}) {
        const payload = {
            reference_id: referenceId,
            amount: amount,
            currency: "IDR",
            payment_method: {
                type: "QR_CODE",
                reusability: "ONE_TIME_USE",
                qrCode: {
                    channelCode: "QRIS"
                }
            },
            metadata: {
                sku: referenceId,
                ...metadata 
            }
        };

        return await this.create(payload);
    }

    /**
     * Mengambil data Payment Request berdasarkan ID
     */
    async getById(paymentRequestId) {
        try {
            if (!paymentRequestId) throw new Error("Payment Request ID is required");
            
            return await this.xenditClient.PaymentRequest.getPaymentRequestByID({
                paymentRequestId
            });
        } catch (error) {
            this._handleError('GET_BY_ID', error);
        }
    }

    /**
     * Standardisasi Error Handling
     */
    _handleError(context, error) {
        logger.error(`[XENDIT_ERROR][${context}]`, error.message);
        
        throw {
            status: 'error',
            context: context,
            message: error.fullError?.message || error.message,
            errorCode: error.fullError?.error_code || 'XENDIT_UNKNOWN_ERROR',
            httpStatus: error.status || 500
        };
    }
}

module.exports = XenditPaymentRequest;