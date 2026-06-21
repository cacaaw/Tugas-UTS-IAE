# User Service Features

Ini adalah penjelasan sederhana fitur yang ada pada `user-service`.

## 1. Auth token beneran
Fitur ini membuat login jadi benar-benar pakai token. Tokennya sederhana, cuma untuk demo, tapi sudah bekerja.

- `register` (registerUser)
  - Buat user baru.
  - User otomatis aktif (`is_active = true`).
  - Server langsung mengembalikan `token`.

- `login`
  - Masuk dengan `email` dan `password`.
  - Kalau berhasil dan user aktif, server mengembalikan `token`.
  - Kalau user `is_active = false`, login ditolak.

- `logout`
  - Pakai `token` untuk logout.
  - Token dicabut sehingga tidak bisa dipakai lagi.

- `me`
  - Ambil data user dari `token`.
  - Fungsinya: cek siapa user yang sedang masuk.

## 2. User order summary
Fitur ini menambahkan ringkasan order saat sudah ada data `userWithOrders`.

- `total order` = jumlah pesanan user.
- `total belanja` = jumlah uang semua pesanan user.
- `order terakhir` = pesanan terbaru dari user.
- `status order aktif` = jumlah order yang masih berjalan/pending.

### Nama GraphQL yang digunakan
- `userWithOrders(id: Int!)` → ambil user + daftar order.
- `userOrderSummary(id: Int!)` → ambil user + ringkasan order.

## 3. Status akun
Fitur ini membuat admin bisa mengaktifkan atau menonaktifkan user.

- `admin bisa nonaktifkan user`
  - Mutation: `updateUserStatus(id: Int!, is_active: Boolean!)`.
  - Contoh: `updateUserStatus(id: 2, is_active: false)`.

- `user yang is_active = false tidak bisa login`
  - Ketika user dimatikan, server menolak `login`.
  - Server juga mencabut token lama sehingga token tidak berlaku lagi.

## 4. Contoh penggunaan GraphQL

### Register user
```graphql
mutation {
  registerUser(name: "Budi", email: "budi@example.test", password: "rahasia") {
    user { id name email is_active }
    token
    token_type
  }
}
```

### Login
```graphql
query {
  login(email: "budi@example.test", password: "rahasia") {
    user { id name email is_active }
    token
    token_type
  }
}
```

### Me
```graphql
query {
  me(token: "TOKEN_YANG_DIDAPAT") {
    id
    name
    email
    is_active
  }
}
```

### Logout
```graphql
mutation {
  logout(token: "TOKEN_YANG_DIDAPAT") {
    success
    message
  }
}
```

### Update status user
```graphql
mutation {
  updateUserStatus(id: 2, is_active: false) {
    id
    name
    is_active
  }
}
```

### Order summary
```graphql
query {
  userOrderSummary(id: 2) {
    user { id name email }
    summary {
      total_orders
      total_spent
      average_order_value
      active_orders
      latest_order { id total_price status created_at }
    }
  }
}
```

## 5. Catatan singkat
- Semua fitur ini sudah ada di kode `user-service`.
- Fitur auth menggunakan token sederhana untuk demo.
- User yang nonaktif tidak bisa login lagi.
- Order summary sudah menghitung total, belanja, order terakhir, dan status aktif.
- File ini dibuat supaya kamu bisa baca dengan lebih mudah tanpa melihat kode langsung.
