# Hasura, RabbitMQ, PostgreSQL, and Docker Setup

Dokumen ini adalah panduan run project IAE setelah tiga GraphQL service Laravel selesai dibuat dan database aplikasi dipindahkan ke PostgreSQL.

## 1. Run Semua Service

```powershell
docker compose up -d --build
```

Service utama:

```text
User Service      : http://localhost:8001
Food Service      : http://localhost:8002
Order Service     : http://localhost:8003
Hasura Console    : http://localhost:8081/console
Hasura GraphQL    : http://localhost:8081/v1/graphql
RabbitMQ UI       : http://localhost:15672
```

Database PostgreSQL:

```text
User DB  : localhost:5433 -> user_db
Food DB  : localhost:5434 -> food_db
Order DB : localhost:5435 -> order_db
User/pass: iae / iae
```

Credential lain:

```text
RabbitMQ username/password : iae / iae
Hasura admin secret        : iae_admin
```

`hasura-init` memang akan `Exited (0)` setelah apply metadata. Itu normal.

## 2. Seed Data

Setelah container hidup, jalankan:

```powershell
.\docker_seed_all.ps1
```

Script ini reset dan seed `user-service` serta `food-service`, reset `order-service`, lalu apply ulang metadata Hasura.

## 3. Verifikasi Health Check

```powershell
curl.exe http://localhost:8001/api/health
curl.exe http://localhost:8002/api/health
curl.exe http://localhost:8003/api/health
```

Expected:

```json
{"status":"ok","service":"user-service"}
{"status":"ok","service":"food-service"}
{"status":"ok","service":"order-service"}
```

## 4. GraphQL Laravel Service

Endpoint GraphQL manual dari Lighthouse:

```text
http://localhost:8001/graphql
http://localhost:8002/graphql
http://localhost:8003/graphql
```

Contoh query Laravel GraphQL:

```graphql
query {
  users(first: 5) {
    data { id name email }
    total
  }
}
```

## 5. Hasura Auto-Track PostgreSQL

Hasura sekarang langsung connect ke tiga database PostgreSQL aplikasi dan auto-generate GraphQL dari tabel:

```text
user_db  -> public.users
food_db  -> public.foods
order_db -> public.orders
```

Metadata otomatis disimpan di:

```text
hasura/metadata.json
```

Contoh query dari Hasura Console:

```graphql
query {
  users(limit: 5) {
    id
    name
    email
  }
  foods(limit: 5) {
    id
    name
    price
    qty
  }
  orders(limit: 5) {
    id
    user_id
    food_id
    quantity
    total_price
    status
  }
}
```

## 6. RabbitMQ Async Order

`order-service` memakai RabbitMQ sebagai queue driver, dan `order-worker` memproses job `App\Jobs\ProcessOrder`.

Alur:

```text
createOrder mutation
-> order dibuat status pending
-> job masuk queue iae_orders
-> order-worker ambil job
-> worker panggil food-service untuk kurangi qty
-> order berubah menjadi created
```

Contoh mutation di `http://localhost:8003/graphql`:

```graphql
mutation {
  createOrder(user_id: 1, food_id: 1, quantity: 2) {
    success
    message
    error
    order {
      id
      quantity
      total_price
      status
    }
  }
}
```

Cek worker:

```powershell
docker compose logs --tail=120 order-worker
```

Cek queue RabbitMQ:

```powershell
curl.exe -u iae:iae http://localhost:15672/api/queues/%2F/iae_orders
```

## 7. Opsi Azure PostgreSQL

Kalau dosen meminta database hidup di Azure, arsitektur ini sudah siap diarahkan ke Azure Database for PostgreSQL. Yang perlu diganti nanti:

```text
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
USER_DATABASE_URL
FOOD_DATABASE_URL
ORDER_DATABASE_URL
```

Untuk demo lokal dan kerja tim, Docker PostgreSQL tetap lebih cepat dan stabil. Untuk demo cloud, gunakan Azure PostgreSQL sebagai pengganti tiga container DB atau satu server PostgreSQL dengan tiga database terpisah.

## 8. Stop dan Reset

Stop container tanpa hapus data:

```powershell
docker compose down
```

Stop dan hapus volume database:

```powershell
docker compose down -v
```

Setelah `down -v`, jalankan ulang:

```powershell
docker compose up -d --build
.\docker_seed_all.ps1
```
