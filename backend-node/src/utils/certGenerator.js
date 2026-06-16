
const crypto = require('crypto');
const fs = require('fs');
const path = require('path');

function generateSelfSignedCert() {
  try {
    // Create ssl directory if it doesn't exist
    const sslDir = path.join(__dirname, '../../ssl');
    if (!fs.existsSync(sslDir)) {
      fs.mkdirSync(sslDir, { recursive: true });
    }
    
    const keyPath = path.join(sslDir, 'key.pem');
    const certPath = path.join(sslDir, 'cert.pem');
    
    // Check if certs already exist
    if (fs.existsSync(keyPath) && fs.existsSync(certPath)) {
      return {
        key: fs.readFileSync(keyPath),
        cert: fs.readFileSync(certPath)
      };
    }
    
    logger.info('🔐 Generating self-signed SSL certificate...');
    
    // Generate RSA key pair
    const { privateKey, publicKey } = crypto.generateKeyPairSync('rsa', {
      modulusLength: 2048,
      publicKeyEncoding: {
        type: 'spki',
        format: 'pem'
      },
      privateKeyEncoding: {
        type: 'pkcs8',
        format: 'pem'
      }
    });
    
    // Create certificate
    const cert = crypto.createCertificate({
      issuer: {
        C: 'ID',
        ST: 'Jakarta',
        L: 'Jakarta',
        O: 'ChessApp Development',
        OU: 'Development',
        CN: 'localhost'
      },
      subject: {
        C: 'ID',
        ST: 'Jakarta',
        L: 'Jakarta',
        O: 'ChessApp Development',
        OU: 'Development',
        CN: 'localhost'
      },
      serialNumber: '01',
      notBefore: new Date(),
      notAfter: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000), // 1 year
      publicKey: publicKey,
      extensions: [
        {
          name: 'subjectAltName',
          altNames: [
            { type: 2, value: 'localhost' },
            { type: 2, value: '127.0.0.1' },
            { type: 2, value: '192.168.1.13' },
            { type: 7, ip: '127.0.0.1' }
          ]
        }
      ]
    });
    
    const certPem = cert.sign(privateKey, 'sha256');
    
    // Save to files
    fs.writeFileSync(keyPath, privateKey);
    fs.writeFileSync(certPath, certPem);
    
    logger.info('✅ Self-signed certificate generated and saved to ssl/ directory');
    
    return {
      key: privateKey,
      cert: certPem
    };
    
  } catch (error) {
    logger.error('❌ Failed to generate self-signed certificate:', error);
    return null;
  }
}

module.exports = { generateSelfSignedCert };
