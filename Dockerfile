# Use uma imagem oficial do Node.js como base
FROM node:20.11.1

# Define o diretório de trabalho dentro do container
WORKDIR /usr/src/app

# Copia o restante dos arquivos da aplicação
COPY . .

# Instala as dependências
RUN npm install

# Expõe a porta usada pela aplicação
EXPOSE 3000

# Comando para iniciar o servidor
CMD ["node", "server.js"]
