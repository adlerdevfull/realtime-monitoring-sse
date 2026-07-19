# Real-Time Monitoring Dashboard with SSE


> **Languages / Idiomas / Idiomas:** [English](#-english) · [Español](#-español) · [Português](#-português)

---

## 🇬🇧 English

API with Server-Sent Events for real-time event delivery — monitoring orders, stock, tasks and system alerts.

### Stack

- **Backend**: PHP 8.2 + Laravel 11
- **Database**: PostgreSQL 16
- **Cache / Pub-Sub**: Redis 7
- **Streaming**: SSE (Server-Sent Events)
- **Auth**: JWT
- **Infra**: Docker + docker-compose + GitHub Actions CI

### Architecture

Hexagonal (Ports & Adapters) with tactical DDD:

```
src/Domain/         → Pure business rules
src/Application/    → Use cases (Command Handlers)
src/Infrastructure/ → Eloquent, Redis Pub/Sub, SSE
app/                → HTTP layer (Controllers, Resources)
```

### Delivery phases

This repository was built in progressive phases (see commit history):

| Phase | Focus |
|-------|--------|
| 1. Scaffold | Bootstrap Laravel API, tooling and ignore rules |
| 2. Domain | Business entities, value objects and repository ports |
| 3. Application | Use-case handlers (commands / services) |
| 4. Infrastructure | Eloquent adapters, Redis and providers |
| 5. API | HTTP controllers, requests, resources and routes |
| 6. Database | Migrations and seeders |
| 7. Config | App, auth, cache, queue configuration |
| 8. Docker | Dockerfile + docker-compose local stack |
| 9. Frontend tooling | Vite, Tailwind and SPA scaffold |
| 10. Frontend UI | Pages, hooks and API client |
| 11. Tests | Unit and feature tests |
| 12. Docs & CI | README multi-language and GitHub Actions |
| 13. SSE stream | Long-lived Server-Sent Events channel |
| 14. Redis pub/sub | Fan-out of events to connected clients |

### How to run

```bash
cp .env.example .env
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret
docker compose exec app php artisan migrate --seed
```

- API: http://localhost:8000/api/v1

**Login**: admin@platform.test / password

### Connecting to SSE

```javascript
const eventSource = new EventSource('/api/v1/events', {
  headers: { 'Authorization': `Bearer ${token}` }
});

eventSource.addEventListener('order.updated', (e) => {
  console.log(JSON.parse(e.data));
});
```

### Event types

- `order.updated` — order status change
- `stock.changed` — stock variation
- `task.completed` — process completion
- `system.alert` — system warnings

### Key features

- Native SSE — `text/event-stream` with correct headers
- Reconnection — `Last-Event-ID` support to resume without data loss
- Persistence — last 50 events per user stored in Redis
- Connection limit — max 3 simultaneous connections per user
- Heartbeat — ping every 15s to keep connection alive
- Nginx optimised — `fastcgi_buffering off` for SSE

### Tests

```bash
docker compose exec app vendor/bin/pest --coverage --min=75
```

---

## 🇪🇸 Español

API con Server-Sent Events para entrega de eventos en tiempo real — monitorización de pedidos, stock, tareas y alertas del sistema.

### Stack

- **Backend**: PHP 8.2 + Laravel 11
- **Base de datos**: PostgreSQL 16
- **Caché / Pub-Sub**: Redis 7
- **Streaming**: SSE (Server-Sent Events)
- **Auth**: JWT
- **Infra**: Docker + docker-compose + GitHub Actions CI

### Arquitectura

Hexagonal (Ports & Adapters) con DDD táctico:

```
src/Domain/         → Reglas de negocio puras
src/Application/    → Casos de uso (Command Handlers)
src/Infrastructure/ → Eloquent, Redis Pub/Sub, SSE
app/                → Capa HTTP (Controllers, Resources)
```

### Fases de entrega

Este repositorio se construyó en fases progresivas (ver historial de commits):

| Fase | Enfoque |
|-------|--------|
| 1. Scaffold | Bootstrap de la API Laravel, tooling e ignore rules |
| 2. Dominio | Entidades, value objects e interfaces de repositorio |
| 3. Aplicación | Handlers de casos de uso (commands / services) |
| 4. Infraestructura | Adapters Eloquent, Redis y providers |
| 5. API | Controllers HTTP, requests, resources y rutas |
| 6. Base de datos | Migraciones y seeders |
| 7. Config | Configuración de app, auth, cache y colas |
| 8. Docker | Dockerfile + docker-compose local |
| 9. Frontend tooling | Vite, Tailwind y scaffold SPA |
| 10. Frontend UI | Páginas, hooks y cliente API |
| 11. Tests | Tests unitarios y de feature |
| 12. Docs & CI | README multi-idioma y GitHub Actions |
| 13. Stream SSE | Canal Server-Sent Events de larga duración |
| 14. Redis pub/sub | Distribución de eventos a clientes conectados |

### Cómo ejecutar

```bash
cp .env.example .env
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret
docker compose exec app php artisan migrate --seed
```

- API: http://localhost:8000/api/v1

**Login**: admin@platform.test / password

### Conectarse al SSE

```javascript
const eventSource = new EventSource('/api/v1/events', {
  headers: { 'Authorization': `Bearer ${token}` }
});

eventSource.addEventListener('order.updated', (e) => {
  console.log(JSON.parse(e.data));
});
```

### Tipos de eventos

- `order.updated` — cambio de estado de pedido
- `stock.changed` — variación de stock
- `task.completed` — finalización de proceso
- `system.alert` — avisos del sistema

### Funcionalidades clave

- SSE nativo — `text/event-stream` con headers correctos
- Reconexión — soporte a `Last-Event-ID` para retomar sin pérdida de datos
- Persistencia — últimos 50 eventos por usuario almacenados en Redis
- Límite de conexiones — máximo 3 simultáneas por usuario
- Heartbeat — ping cada 15s para mantener la conexión activa
- Nginx optimizado — `fastcgi_buffering off` para SSE

### Tests

```bash
docker compose exec app vendor/bin/pest --coverage --min=75
```

---

## 🇧🇷 Português

API com Server-Sent Events para entrega de eventos em tempo real — monitoramento de pedidos, estoque, tarefas e alertas do sistema.

### Stack

- **Backend**: PHP 8.2 + Laravel 11
- **Banco de dados**: PostgreSQL 16
- **Cache / Pub-Sub**: Redis 7
- **Streaming**: SSE (Server-Sent Events)
- **Auth**: JWT
- **Infra**: Docker + docker-compose + GitHub Actions CI

### Arquitetura

Hexagonal (Ports & Adapters) com DDD tático:

```
src/Domain/         → Regras de negócio puras
src/Application/    → Casos de uso (Command Handlers)
src/Infrastructure/ → Eloquent, Redis Pub/Sub, SSE
app/                → Camada HTTP (Controllers, Resources)
```

### Fases de entrega

Este repositório foi construído em fases progressivas (ver histórico de commits):

| Fase | Foco |
|-------|--------|
| 1. Scaffold | Bootstrap da API Laravel, tooling e ignore rules |
| 2. Domínio | Entidades, value objects e portas de repositório |
| 3. Aplicação | Handlers de casos de uso (commands / services) |
| 4. Infraestrutura | Adapters Eloquent, Redis e providers |
| 5. API | Controllers HTTP, requests, resources e rotas |
| 6. Banco de dados | Migrations e seeders |
| 7. Config | Configuração de app, auth, cache e filas |
| 8. Docker | Dockerfile + docker-compose local |
| 9. Frontend tooling | Vite, Tailwind e scaffold SPA |
| 10. Frontend UI | Páginas, hooks e cliente da API |
| 11. Testes | Testes unitários e de feature |
| 12. Docs & CI | README multi-idioma e GitHub Actions |
| 13. Stream SSE | Canal Server-Sent Events de longa duração |
| 14. Redis pub/sub | Distribuição de eventos aos clientes conectados |

### Como executar

```bash
cp .env.example .env
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret
docker compose exec app php artisan migrate --seed
```

- API: http://localhost:8000/api/v1

**Login**: admin@platform.test / password

### Conectar ao SSE

```javascript
const eventSource = new EventSource('/api/v1/events', {
  headers: { 'Authorization': `Bearer ${token}` }
});

eventSource.addEventListener('order.updated', (e) => {
  console.log(JSON.parse(e.data));
});
```

### Tipos de eventos

- `order.updated` — alteração de status de pedido
- `stock.changed` — variação de estoque
- `task.completed` — finalização de processo
- `system.alert` — avisos do sistema

### Funcionalidades principais

- SSE nativo — `text/event-stream` com headers corretos
- Reconexão — suporte a `Last-Event-ID` para retomar sem perda de dados
- Persistência — últimos 50 eventos por usuário armazenados no Redis
- Limite de conexões — máximo 3 simultâneas por usuário
- Heartbeat — ping a cada 15s para manter conexão viva
- Nginx otimizado — `fastcgi_buffering off` para SSE

### Testes

```bash
docker compose exec app vendor/bin/pest --coverage --min=75
```
