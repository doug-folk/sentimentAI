# 🔍 Sistema de Análise de Sentimento para Redes Sociais

Este projeto é uma aplicação web que permite **analisar sentimentos** em postagens e comentários de redes sociais, como **Facebook**, utilizando **Inteligência Artificial**. A aplicação oferece também um **dashboard interativo**, exportação de relatórios, e arquitetura moderna com Docker.

---

## 📌 Funcionalidades

- ✅ Entrada de postagens/comentários
- 🤖 Análise automática com IA (positivo, neutro, negativo)
- 📊 Dashboard com gráficos e estatísticas
- 📤 Exportação de relatórios (CSV/JSON)
- 🌐 Integração com APIs sociais (Facebook)
- 🐳 Arquitetura com Docker para ambiente isolado

---

## 🛠️ Tecnologias Utilizadas

| Camada       | Tecnologias                     |
|--------------|----------------------------------|
| Backend      | PHP 8.2 (Laravel)               |
| IA           | Hugging Face API (NLP)         |
| Banco        | PostgreSQL 15                  |
| Frontend     | Blade + Bootstrap              |
| Infraestrutura | Docker, Docker Compose, Nginx |

---

## 🚀 Como Rodar o Projeto

### ✅ Pré-requisitos

- **Docker** e **Docker Compose** (atualizados)
- **Git**
- (Opcional) **Git Bash** ou **PowerShell 7+** no Windows

---

### 🔄 Passos (Linux/macOS)

```bash
git clone https://github.com/seu-usuario/seu-repositorio.git
cd seu-repositorio

# Execute os containers
docker compose up -d --build
