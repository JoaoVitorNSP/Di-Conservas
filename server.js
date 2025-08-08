const express = require('express');
const multer = require('multer');
const path = require('path');
const fs = require('fs');

const app = express();
const PORT = process.env.PORT || 3000;

// Configuração do Multer para salvar imagens em 'assets/'
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        cb(null, path.join(__dirname, 'assets'));
    },
    filename: function (req, file, cb) {
        // Salva com nome original, mas pode customizar
        cb(null, Date.now() + '-' + file.originalname);
    }
});
const upload = multer({ storage });

// Middleware para servir arquivos estáticos
app.use('/assets', express.static(path.join(__dirname, 'assets')));
app.use(express.urlencoded({ extended: true }));

// Servir index.html na raiz
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'index.html'));
});

// Rota para receber formulário POST
app.post('/upload', upload.single('image'), (req, res) => {
    const { name, category, description, weight, retailPrice, wholesalePrice } = req.body;
    const image = req.file ? req.file.filename : null;

    // Salvar dados em um arquivo JSON (opcional)
    const product = {
        name,
        category,
        description,
        weight,
        retailPrice,
        wholesalePrice,
        image
    };

    // Adiciona ao arquivo products.json
    const productsFile = path.join(__dirname, 'products.json');
    let products = [];
    if (fs.existsSync(productsFile)) {
        products = JSON.parse(fs.readFileSync(productsFile));
    }
    products.push(product);
    fs.writeFileSync(productsFile, JSON.stringify(products, null, 2));

    res.send('Produto cadastrado com sucesso!');
});

app.listen(PORT, () => {
    console.log(`Servidor rodando em http://localhost:${PORT}`);
});
