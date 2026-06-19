# Tugas UTS IAE - Microservices Food Order

Project ini adalah implementasi microservices untuk sistem pemesanan makanan. Stack utama yang dipakai:

- Laravel untuk `user-service`, `food-service`, dan `order-service`
- PostgreSQL sebagai database terpisah untuk setiap service
- Hasura untuk auto-generated GraphQL dari tabel PostgreSQL
- RabbitMQ untuk asynchronous order processing
- Docker Compose agar semua anggota tim bisa menjalankan environment yang sama

## Arsitektur

```text
User Service  : http://localhost:8001
Food Service  : http://localhost:8002
Order Service : http://localhost:8003
Hasura        : http://localhost:8081/console
RabbitMQ      : http://localhost:15672
```

Database PostgreSQL:

```text
user-db  : localhost:5433 -> user_db
food-db  : localhost:5434 -> food_db
order-db : localhost:5435 -> order_db
```

Credential default:

```text
PostgreSQL user/password : iae / iae
RabbitMQ user/password   : iae / iae
Hasura admin secret      : iae_admin
```

## Cara Menjalankan

Pastikan Docker Desktop sudah berjalan, lalu jalankan dari folder root project:

```powershell
docker compose up -d --build
.\docker_seed_all.ps1
docker compose ps
```

`hasura-init` akan selesai lalu statusnya `Exited (0)`. Itu normal karena tugasnya hanya apply metadata Hasura.

## Verifikasi

Health check:

```powershell
curl.exe http://localhost:8001/api/health
curl.exe http://localhost:8002/api/health
curl.exe http://localhost:8003/api/health
```

Hasura Console:

```text
http://localhost:8081/console
```

Masukkan admin secret:

```text
iae_admin
```

Contoh query Hasura:

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

## Demo RabbitMQ Async Order

Gunakan Postman, Insomnia, atau GraphQL client lain untuk mengirim request POST ke GraphQL Order Service:

```text
http://localhost:8003/graphql
```

Jangan membuka URL tersebut langsung di browser tanpa query, karena endpoint akan membalas error `GraphQL Request must include query`.

Jalankan mutation:

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

Alurnya:

```text
createOrder
-> order dibuat status pending
-> job masuk RabbitMQ queue iae_orders
-> order-worker memproses job
-> stok food berkurang
-> order berubah menjadi created
```

Cek worker:

```powershell
docker compose logs --tail=120 order-worker
```

Cek RabbitMQ UI:

```text
http://localhost:15672
```

Login:

```text
iae / iae
```

## Dokumentasi Tambahan

- `HASURA_RABBITMQ_DOCKER.md` berisi panduan teknis Docker, Hasura, PostgreSQL, dan RabbitMQ.
- Schema GraphQL manual setiap service ada di folder `graphql/schema.graphql` masing-masing service.

## Stop dan Reset

Stop container:

```powershell
docker compose down
```

Stop sekaligus hapus data volume:

```powershell
docker compose down -v
```

Setelah reset volume, jalankan ulang:

```powershell
docker compose up -d --build
.\docker_seed_all.ps1
```
