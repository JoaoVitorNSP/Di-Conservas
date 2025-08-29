# Docker Setup para Di Conservas - Estrutura MySQL

## Inicialização Completa

Para inicializar o projeto com a nova estrutura MySQL, execute:

```bash
# Remove containers e volumes existentes (CUIDADO: apaga dados)
docker-compose down -v

# Reconstrói e inicia os containers
docker-compose up --build -d

# Verifica os logs
docker-compose logs -f app
```

## Estrutura do Banco

O projeto agora usa MySQL com as seguintes tabelas:
- `measurement_units` - Unidades de medida (kg, g, ml, L, un, pct, cx)
- `categories` - Categorias de produtos
- `products` - Produtos com UUID, preços varejo/atacado, peso, etc.

## Inicialização Automática

O sistema foi configurado para:
1. Aguardar o MySQL estar disponível
2. Verificar se as tabelas existem
3. Executar migração automaticamente se necessário
4. Popular com dados iniciais
5. Configurar permissões
6. Iniciar o Apache

## Comandos Úteis

```bash
# Ver logs da aplicação
docker-compose logs app

# Ver logs do MySQL
docker-compose logs mysql

# Acessar container da aplicação
docker-compose exec app bash

# Acessar MySQL
docker-compose exec mysql mysql -u di_user -p di_conservas

# Resetar banco de dados
docker-compose down -v && docker-compose up -d
```

## Portas

- Aplicação: http://localhost:80
- MySQL: localhost:3306

## Credenciais MySQL

- Database: di_conservas
- User: di_user
- Password: di_pass
- Root Password: rootpass

## Estrutura de Arquivos

```
database/
├── init-complete.sql      # Migração e dados iniciais
├── migrations/           # Migrações separadas
└── seeds/               # Dados de exemplo

scripts/
├── start-app.sh         # Script de inicialização
└── wait-for-mysql.sh    # Script de espera do MySQL
```
