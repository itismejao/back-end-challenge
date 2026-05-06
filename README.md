# OneRPM - Track Explorer

Sistema de agregação de metadados de faixas musicais que consome a API do Spotify para buscar, armazenar e exibir informações de tracks por ISRC (International Standard Recording Code).

## O que o projeto faz

- Busca metadados de faixas musicais na API do Spotify a partir de códigos ISRC
- Armazena artistas, álbums, tracks e seus relacionamentos em banco relacional
- Verifica disponibilidade das faixas por mercado (país)
- Exibe as faixas em um frontend Angular com player de preview do Spotify
- Registra logs de cada integração (sucesso, falha, tempo de execução)

## Arquitetura

O projeto segue **Domain-Driven Design (DDD)** com três bounded contexts:

```
app/Domain/
  Music/          -> Core domain (tracks, álbums, artistas, enums, cache, observers)
  Integration/    -> Integração com providers externos (Spotify, jobs, logs, DTOs)
  Shared/         -> Entidades transversais (countries)
```

Cada domínio possui seu próprio **ServiceProvider**, rotas, controllers, requests, resources e testes.

### Padrão Strategy

A integração com providers de música usa o padrão Strategy:
- `MusicProviderInterface` define o contrato
- `SpotifyMusicProvider` implementa para o Spotify
- `MusicProviderFactory` resolve o provider pelo código
- Para adicionar outro provider (Deezer, Tidal), bastaria criar uma nova classe implementando a interface

## Serviços (Docker)

| Serviço | Imagem | Porta | Função |
|---------|--------|-------|--------|
| **app** | PHP 8.4 + Swoole | 8000 (interno) | Laravel Octane - API e backend |
| **nginx** | Nginx 1.27 | 80, 443 | Reverse proxy, serve frontend Angular |
| **mysql** | MySQL 8.4 | 3306 | Banco de dados relacional |
| **redis** | Redis 7 | 6379 | Cache, filas e sessões |
| **worker** | PHP 8.4 + Supervisor | - | Processamento de jobs (fila `integration`) |

## Pré-requisitos

- Docker e Docker Compose

## Como rodar

### 1. Clone o repositório

```bash
git clone <repo-url>
cd one-rpm
```

### 2. Configure o ambiente

```bash
cp .env.example .env
```

Edite o `.env` e preencha:

```env
DB_PASSWORD=sua_senha
DB_ROOT_PASSWORD=sua_senha_root
SPOTIFY_CLIENT_ID=seu_client_id
SPOTIFY_CLIENT_SECRET=seu_client_secret
```

### 3. Suba os containers

```bash
docker compose up -d
```

O entrypoint automaticamente:
- Roda as migrations
- Popula countries (250 países ISO) e providers (Spotify, Apple Music, Deezer, Tidal)
- Cria o banco de testes (`onerpm_testing`)
- Importa 10 faixas de exemplo do Spotify com mercados BR, US e GB

### 4. Acesse

- **Frontend:** http://localhost
- **API:** http://localhost/api/tracks?market=BR

### 5. Rode os testes

```bash
docker compose exec app php artisan test
```

Os testes rodam em banco isolado (`onerpm_testing`), sem afetar os dados.

## Páginas do frontend

| URL | Descrição |
|-----|-----------|
| `http://localhost/` | **Track Explorer** - Listagem de faixas com filtros (ordenação, mercado), cards com thumb do álbum, duração, artistas, badge de disponibilidade, player Spotify embed |
| `http://localhost/logs` | **Integration Logs** - Tabela de logs de integração com filtros por status, ISRC e paginação. Click para expandir detalhes de erro |

## Endpoints da API

### `GET /api/tracks`

Listagem paginada (cursor) de faixas.

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `market` | string(2) | Sim | Código ISO do país (ex: BR, US) |
| `order_by` | string | Não | `title`, `duration`, `release_date`, `artist`, `track_number`, `created_at` (default: `title`) |
| `direction` | string | Não | `asc`, `desc` (default: `asc`) |
| `per_page` | int | Não | 1-100 (default: 15) |

```bash
curl "http://localhost/api/tracks?market=BR&order_by=artist&direction=asc&per_page=5"
```

### `POST /api/tracks/fetch`

Despacha jobs para importar faixas do Spotify.

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `isrcs` | string[] | Sim | Lista de ISRCs (12 caracteres cada, máx 100) |
| `provider` | string | Não | Código do provider (default: `spotify`) |
| `markets` | string[] | Não | Mercados para verificar disponibilidade |

```bash
curl -X POST "http://localhost/api/tracks/fetch" \
  -H "Content-Type: application/json" \
  -d '{"isrcs": ["NO1R42509310"], "markets": ["BR", "US"]}'
```

### `GET /api/integration/logs`

Listagem paginada (cursor) de logs de integração.

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `status` | string | Não | `pending`, `success`, `not_found`, `failed` |
| `isrc` | string(12) | Não | Filtrar por ISRC |
| `provider_code` | string | Não | Filtrar por provider |
| `from` | date | Não | Data inicial |
| `to` | date | Não | Data final |
| `per_page` | int | Não | 1-100 (default: 20) |

```bash
curl "http://localhost/api/integration/logs?status=failed&per_page=10"
```

## Comando Artisan

```bash
# Importar faixas por ISRC
docker compose exec app php artisan tracks:fetch ISRC1 ISRC2 --markets=BR,US,GB

# Usar outro provider
docker compose exec app php artisan tracks:fetch ISRC1 --provider=spotify --markets=BR
```

## Testes

```
65 testes, ~165 assertions

Unit/
  Music/       -> Enums (AlbumType, AvailabilityMode), TrackResource
  Integration/ -> DTOs, SpotifyMusicProvider, MusicProviderFactory, TrackIngestionService

Feature/
  Music/       -> Endpoint GET /api/tracks, Repositories, Cache invalidation (Observers)
  Integration/ -> Endpoint POST /api/tracks/fetch
```

## Tecnologias

- **Backend:** PHP 8.4, Laravel 13, Octane (Swoole)
- **Frontend:** Angular 19, TypeScript
- **Banco:** MySQL 8.4
- **Cache/Filas:** Redis 7
- **Infra:** Docker, Nginx, Supervisor
