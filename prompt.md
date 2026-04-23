KONTEKS:
Saya memiliki sistem booking villa berbasis Laravel + Filament yang sudah berjalan.

Namun saya ingin Anda melakukan AUDIT MENYELURUH terhadap sistem ini, lalu memperbaiki semua masalah yang ditemukan.

Fokus utama:
- Bug
- Konsistensi flow booking
- Keamanan sistem (ANTI MANIPULASI USER)

JANGAN membuat ulang project.
JANGAN mengubah struktur besar tanpa alasan kuat.
Lakukan perbaikan secara minimal (surgical fix).

----------------------------------

FLOW RESMI YANG HARUS DIJAGA:

1. User submit booking → status = "pending"
2. Admin ubah → "approved"
3. User bisa bayar → upload bukti → status booking = "paid"
4. Admin verifikasi → status booking = "confirmed"
5. User mendapatkan invoice

----------------------------------

PRINSIP KEAMANAN (WAJIB DIPATUHI):

1. USER TIDAK BOLEH:
   - mengubah status booking secara manual (via request / URL / form)
   - mengakses booking milik user lain
   - mengubah booking menjadi "approved", "paid", atau "confirmed"
   - mengirim request palsu untuk bypass pembayaran

2. PAYMENT HARUS:
   - hanya bisa dibuat untuk booking milik user yang login
   - hanya bisa dibuat jika booking status = "approved"
   - tidak boleh overwrite booking lain

3. BOOKING STATUS:
   - tidak boleh lompat langsung:
     pending → confirmed (HARUS lewat approved & paid)
   - semua perubahan status harus tervalidasi

4. ADMIN ACTION:
   - hanya admin yang bisa:
     approved / rejected
     confirmed payment
   - validasi harus dilakukan di backend, bukan hanya UI

----------------------------------

TUGAS ANDA:

1. 🔍 AUDIT SEMUA FILE TERKAIT:
   - BookingController
   - PaymentController
   - Model Booking & Payment
   - Filament Resources
   - Routes

2. 🧠 TEMUKAN MASALAH:
   - kemungkinan user bypass flow
   - mass assignment vulnerability
   - missing validation
   - broken authorization
   - status inconsistency
   - file upload issue

3. 🔐 PERBAIKI KEAMANAN:
   - gunakan authorization check (auth()->id)
   - validasi kepemilikan booking
   - pastikan tidak bisa booking orang lain
   - pastikan tidak bisa inject status dari request

4. 🔄 PERBAIKI FLOW:
   - enforce urutan status:
     pending → approved → paid → confirmed
   - tambahkan guard agar tidak lompat status

5. 🖼️ PERBAIKI BUKTI BAYAR:
   - pastikan upload masuk ke storage/public
   - tampilkan di Filament (ImageColumn)
   - validasi file (image only, max size)

6. ⚙️ PERBAIKI PAYMENT LOGIC:
   - saat payment dibuat:
       → booking.status = "paid"
   - saat admin confirm:
       → booking.status = "confirmed"

7. 🚫 BLOKIR CHEAT USER:
   - user tidak bisa:
       → kirim POST manual ke /payments untuk booking lain
       → ubah status lewat request
       → skip payment

----------------------------------

LEVEL PRIORITAS:

1. SECURITY (paling penting)
2. FLOW CONSISTENCY
3. BUG FIX
4. UI (opsional)

----------------------------------

CATATAN:

Berpikir seperti attacker (malicious user).
Asumsikan user mencoba:
- manipulasi request
- ubah URL
- kirim data manual via Postman

Pastikan sistem tetap aman.
