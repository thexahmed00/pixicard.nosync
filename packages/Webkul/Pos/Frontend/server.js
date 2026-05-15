import express from 'express';
import dotenv from 'dotenv';
import path from 'path';
import cors from 'cors';
import fs from 'fs';
import https from 'https';
import { fileURLToPath } from 'url';

const app = express();
dotenv.config();

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

app.use(cors({
    origin: '*',
    methods: ['POST'],
    allowedHeaders: ['Content-Type', 'Authorization', 'x-locale', 'x-currency'],
}));

app.use(express.static('dist'));

const port = process.env.VITE_PORT || 5000;
const host = process.env.VITE_HOST || 'localhost';
const sslEnabled = process.env.SSL_ENABLED === 'true';

const sslOptions = sslEnabled ? {
    key: fs.readFileSync(process.env.SSL_KEY_PATH || 'path/to/your/private.key'),
    cert: fs.readFileSync(process.env.SSL_CERT_PATH || 'path/to/your/certificate.crt')
} : null;

app.get('*', (req, res) => {
    res.sendFile(path.join(__dirname, 'dist', 'index.html'));
});

if (
    sslEnabled
    && sslOptions
) {
    https.createServer(sslOptions, app).listen(port, () => {
        console.log(`HTTPS Server running on port ${port}. Now you can access your frontend at: https://${host}:${port}`);
    });
} else {
    app.listen(port, () => {
        console.log(`HTTP Server running on port ${port}. Now you can access your frontend at: http://${host}:${port}`);
    });
}
