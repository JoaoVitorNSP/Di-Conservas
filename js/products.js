// Este script será responsável por buscar os dados dos produtos e renderizá-los na página.

document.addEventListener('DOMContentLoaded', () => {
    const productsGrid = document.getElementById('products-grid');

    // Função assíncrona para carregar os produtos da nossa função serverless
    const loadProducts = async () => {
        if (!productsGrid) return;

        try {
            // Faz uma requisição para a Netlify Function que criamos.
            // Ela vai ler todos os arquivos JSON da pasta _data/products e retorná-los.
            const response = await fetch('/.netlify/functions/get-products');
            if (!response.ok) {
                throw new Error(`A requisição falhou com o status: ${response.status}`);
            }
            const products = await response.json();

            // Limpa a mensagem de "Carregando..."
            productsGrid.innerHTML = '';

            // Se não houver produtos, exibe uma mensagem amigável
            if (products.length === 0) {
                productsGrid.innerHTML = '<p class="col-span-full text-center">Nenhum produto cadastrado no momento. Volte em breve!</p>';
                return;
            }

            // Cria o HTML para cada produto e o insere na grade
            products.forEach(product => {
                const precoVarejo = product.preco_varejo ? `R$ ${product.preco_varejo.toFixed(2).replace('.', ',')}` : 'Consulte';
                const precoAtacado = product.preco_atacado ? `R$ ${product.preco_atacado.toFixed(2).replace('.', ',')}` : 'Consulte';

                const productCardHTML = `
                    <div class="product-card">
                        <div class="product-image">
                            <img src="${product.imagem}" alt="Imagem de ${product.nome}" onerror="this.src='https://placehold.co/400x400/f0f0f0/ccc?text=Imagem indisp.'">
                        </div>
                        <div class="product-info">
                            <h5 class="product-category">${product.categoria}</h5>
                            <h4 class="product-title">${product.nome}</h4>
                            <p class="product-description">${product.descricao || ''}</p>
                            <p class="product-weight">Peso: ${product.peso}</p>
                            <div class="product-price">
                                <span>Varejo: ${precoVarejo}</span>
                                <span>Atacado: ${precoAtacado}</span>
                            </div>
                        </div>
                    </div>
                `;
                productsGrid.insertAdjacentHTML('beforeend', productCardHTML);
            });

        } catch (error) {
            console.error("Erro ao carregar os produtos:", error);
            productsGrid.innerHTML = '<p class="col-span-full text-center">Não foi possível carregar os produtos. Por favor, tente novamente mais tarde.</p>';
        }
    };

    // Inicia o carregamento dos produtos
    loadProducts();
});
