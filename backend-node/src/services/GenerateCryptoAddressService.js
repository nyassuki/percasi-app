const ethers = require('ethers');
const TronWeb = require('tronweb');
const bitcoin = require('bitcoinjs-lib');
const { ECPairFactory } = require('ecpair');
const tinysecp = require('tiny-secp256k1');
const CryptoJS = require('crypto-js');

const ECPair = ECPairFactory(tinysecp);

class GenerateCryptoAddressService {
    constructor(encryptionKey) {
        if (!encryptionKey) throw new Error("Encryption Key wajib diisi!");
        this.encryptionKey = encryptionKey;
        this.tronWeb = new TronWeb({ fullHost: 'https://api.trongrid.io' });
    }

    /**
     * Helper: Enkripsi Private Key
     */
    _encrypt(privateKey) {
        return CryptoJS.AES.encrypt(privateKey, this.encryptionKey).toString();
    }

    /**
     * Generator untuk ERC20 & BEP20 (Ethereum & BSC)
     * Format alamat sama (0x...)
     */
    async _generateEVM() {
        const wallet = ethers.Wallet.createRandom();
        return {
            address: wallet.address,
            encryptedPrivateKey: this._encrypt(wallet.privateKey)
        };
    }

    /**
     * Generator untuk TRC20 (Tron)
     * Format alamat T...
     */
    async _generateTron() {
        const account = await this.tronWeb.createAccount();
        return {
            address: account.address.base58,
            encryptedPrivateKey: this._encrypt(account.privateKey)
        };
    }

    /**
     * Generator untuk Bitcoin (Legacy/P2PKH)
     */
    async _generateBTC() {
        const keyPair = ECPair.makeRandom();
        const { address } = bitcoin.payments.p2pkh({ pubkey: keyPair.publicKey });
        return {
            address: address,
            encryptedPrivateKey: this._encrypt(keyPair.toWIF())
        };
    }

    /**
     * FUNGSI UTAMA: Menyesuaikan dengan pilihan UI
     * @param {string} network - TRC20, ERC20, BEP20, atau BTC
     */
    async create(network) {
        const net = network.toUpperCase();
        let result;

        switch (net) {
            case 'TRC20':
                result = await this._generateTron();
                break;
            case 'ERC20':
            case 'BEP20':
                result = await this._generateEVM();
                break;
            case 'BTC':
            case 'BITCOIN':
                result = await this._generateBTC();
                break;
            default:
                throw new Error("Network tidak didukung");
        }

        return {
            network: net,
            address: result.address,
            encryptedPrivateKey: result.encryptedPrivateKey,
            createdAt: new Date()
        };
    }
}

module.exports = GenerateCryptoAddressService;