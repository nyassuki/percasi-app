// src/services/CryptoMonitorService.js
const { TronWeb } = require('tronweb'); // Perbaikan di sini
const ethers = require('ethers');
const CryptoPaymentModel = require('../models/CryptoPaymentModel');
const logger = require('../utils/logger');

class CryptoMonitorService {
    constructor() {
        this.rpc = {
            BEP20: 'https://bsc-dataseed.binance.org/',
            ERC20: 'https://cloudflare-eth.com',
            TRC20: 'https://api.trongrid.io'
        };

        this.usdtContracts = {
            BEP20: '0x55d398326f99059fF775485246999027B3197955',
            ERC20: '0xdAC17F958D2ee523a2206206994597C13D831ec7',
            TRC20: 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t'
        };

        // Inisialisasi TronWeb dengan proteksi error
        try {
            this.tronWeb = new TronWeb({
                fullHost: this.rpc.TRC20,
                headers: { "TRON-PRO-API-KEY": process.env.TRON_API_KEY || "" } // Opsional jika punya API key
            });
        } catch (e) {
            logger.error("Gagal inisialisasi TronWeb:", e.message);
        }
    }

    async scan() {
        try {
            const activePayments = await CryptoPaymentModel.getActivePayments();
            if (activePayments.length === 0) return;

            logger.info(`[Monitor] Scanning ${activePayments.length} active addresses...`);

            for (const payment of activePayments) {
                if (payment.network === 'TRC20') {
                    await this.checkTronUSDT(payment);
                } else if (payment.network === 'BEP20' || payment.network === 'ERC20') {
                    await this.checkEVMUSDT(payment);
                }
            }
        } catch (error) {
            logger.error("Scan Error:", error);
        }
    }

    async checkTronUSDT(payment) {
        try {
            const contract = await this.tronWeb.contract().at(this.usdtContracts.TRC20);
            const balanceRaw = await contract.balanceOf(payment.address).call();
            const balance = balanceRaw.toNumber() / 1e6;

            if (balance >= payment.amount_expected) {
                logger.info(`[TRC20] Payment Detected: ${payment.address} - ${balance} USDT`);
                await CryptoPaymentModel.updateAsSuccess(payment.id, balance, 'SCAN_RESULT');
            }
        } catch (err) {
            // Silently ignore if address is not active on-chain yet
        }
    }

    async checkEVMUSDT(payment) {
        try {
            const provider = new ethers.providers.JsonRpcProvider(this.rpc[payment.network]);
            const abi = ["function balanceOf(address) view returns (uint256)"];
            const contract = new ethers.Contract(this.usdtContracts[payment.network], abi, provider);

            const balanceRaw = await contract.balanceOf(payment.address);
            // USDT ERC20 = 6 desimal, BEP20 = 18 desimal (biasanya)
            const decimals = payment.network === 'BEP20' ? 18 : 6;
            const balance = parseFloat(ethers.utils.formatUnits(balanceRaw, decimals));

            if (balance >= payment.amount_expected) {
                logger.info(`[${payment.network}] Payment Detected: ${payment.address} - ${balance} USDT`);
                await CryptoPaymentModel.updateAsSuccess(payment.id, balance, 'SCAN_RESULT');
            }
        } catch (err) {
            logger.error(`Error checking EVM ${payment.address}:`, err.message);
        }
    }
}

module.exports = new CryptoMonitorService();