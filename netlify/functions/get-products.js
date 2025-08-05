// netlify/functions/get-products.js
const fs = require('fs').promises;
const path = require('path');

exports.handler = async (event, context) => {
  try {
    const productsPath = path.resolve(process.cwd(), '_data', 'products');
    const files = await fs.readdir(productsPath);

    const products = await Promise.all(
      files
        .filter(file => file.endsWith('.json'))
        .map(async file => {
          const filePath = path.join(productsPath, file);
          const content = await fs.readFile(filePath, 'utf8');
          return JSON.parse(content);
        })
    );

    return {
      statusCode: 200,
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(products),
    };
  } catch (error) {
    console.error('Error reading product data:', error);
    return {
      statusCode: 500,
      body: JSON.stringify({ error: 'Failed to load product data' }),
    };
  }
};