# SentimentAI - Instruções de Instalação e Configuração

## Pré-requisitos

### Sistema
- PHP 8.1 ou superior
- Composer
- Node.js 18+ 
- npm ou pnpm
- MySQL ou PostgreSQL

### APIs Externas
- **Hugging Face API Key** - Necessária para análise de sentimentos
  - Criar conta em: https://huggingface.co/
  - Gerar API key em: https://huggingface.co/settings/tokens

## Configuração do Backend (Laravel)

### 1. Instalar Dependências PHP
```bash
cd sentimentAI
composer install
```

### 2. Configurar Ambiente
```bash
# Copiar arquivo de ambiente
cp .env.example .env

# Gerar chave da aplicação
php artisan key:generate
```

### 3. Configurar Banco de Dados
Editar arquivo `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sentiment_ai
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

# API do Hugging Face
HUGGING_FACE_API_KEY=sua_api_key_aqui
```

### 4. Executar Migrações
```bash
php artisan migrate
```

### 5. Instalar Laravel Sanctum (se não estiver instalado)
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### 6. Configurar CORS
Editar `config/cors.php`:
```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:5173', 'http://localhost:3000'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### 7. Iniciar Servidor Laravel
```bash
php artisan serve
# Servidor rodará em: http://localhost:8000
```

## Configuração do Frontend (React)

### 1. Instalar Dependências
```bash
cd sentiment-ai-frontend
npm install

# Dependências adicionais necessárias:
npm add react-router-dom date-fns recharts
```

### 2. Configurar Variáveis de Ambiente
Criar arquivo `.env` na raiz do projeto React:
```env
VITE_API_BASE_URL=http://localhost:8000/api
```

### 3. Iniciar Servidor de Desenvolvimento
```bash
npm run dev
# Servidor rodará em: http://localhost:5173
```

## Estrutura de Arquivos Criados

### Backend (Laravel)
```
sentimentAI/
├── routes/api.php (NOVO)
├── app/Http/Controllers/
│   ├── AuthController.php (ATUALIZADO)
│   ├── PostagemController.php (ATUALIZADO)
│   └── DashboardController.php (NOVO)
└── config/cors.php (CONFIGURADO)
```

### Frontend (React)
```
sentiment-ai-frontend/
├── src/
│   ├── components/
│   │   ├── ui/ (shadcn/ui components)
│   │   ├── ProtectedRoute.jsx
│   │   └── date-range-picker.jsx
│   ├── contexts/
│   │   └── AuthContext.jsx
│   ├── pages/
│   │   ├── Login.jsx
│   │   ├── Register.jsx
│   │   ├── Dashboard.jsx
│   │   └── NewAnalysis.jsx
│   └── App.jsx (ATUALIZADO)
```

## Funcionalidades Implementadas

### ✅ Sistema de Autenticação
- Login e registro de usuários
- Autenticação via Laravel Sanctum
- Proteção de rotas no React

### ✅ Dashboard Interativo
- Estatísticas gerais de sentimentos
- Gráficos de tendências temporais
- Distribuição de sentimentos (pizza)
- Análise por rede social (barras)
- Filtros por período e data

### ✅ Análise de Sentimentos
- Integração com Hugging Face API
- Suporte a múltiplas redes sociais
- Interface para nova análise

### ✅ Design Responsivo
- Interface moderna com Tailwind CSS
- Componentes shadcn/ui
- Ícones Lucide React
- Responsivo para mobile/desktop

## Como Testar

### 1. Iniciar Backend
```bash
cd sentimentAI
php artisan serve
```

### 2. Iniciar Frontend
```bash
cd sentiment-ai-frontend
pnpm run dev
```

### 3. Acessar Aplicação
- Abrir: http://localhost:5173
- Criar conta ou fazer login
- Testar análise de sentimentos
- Visualizar dashboard com filtros

## Possíveis Problemas e Soluções

### CORS Error
- Verificar configuração em `config/cors.php`
- Confirmar URL do frontend nas origens permitidas

### API Key Hugging Face
- Verificar se a chave está correta no `.env`
- Testar a chave diretamente na API do Hugging Face

### Banco de Dados
- Verificar conexão no `.env`
- Executar migrações: `php artisan migrate`

### Dependências
- Limpar cache: `composer dump-autoload`
- Reinstalar: `rm -rf node_modules && pnpm install`

