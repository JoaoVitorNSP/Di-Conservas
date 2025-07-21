// build-products.js
const fs = require('fs');
const path = require('path');

const productsDir = path.join(__dirname, 'src', 'data', 'products');
const outputFile = path.join(__dirname, 'dist', 'products.js');
const products = [];

// Certifique-se de que a pasta 'dist' exista
const distDir = path.join(__dirname, 'dist');
if (!fs.existsSync(distDir)){
    fs.mkdirSync(distDir);
}

// Lê todos os arquivos JSON na pasta de produtos
fs.readdirSync(productsDir).forEach(file => {
    if (file.endsWith('.json')) {
        const filePath = path.join(productsDir, file);
        const productData = JSON.parse(fs.readFileSync(filePath, 'utf-8'));
        products.push(productData);
    }
});

// Gera o conteúdo do arquivo JavaScript
const jsContent = `let products = ${JSON.stringify(products, null, 2)};\n`;

// Escreve o arquivo products.js
fs.writeFileSync(outputFile, jsContent, 'utf-8');
console.log(`products.js gerado com ${products.length} produtos.`);