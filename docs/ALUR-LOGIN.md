# Alur Login — PT Mustika Makmur Abadi

Dokumen ini mencatat alur yang ditemukan pada kode saat diperiksa. Route login/logout didaftarkan Fortify saat runtime; verifikasi route dilakukan dengan `php artisan route:list --path=login`, `--path=logout`, dan `--path=two-factor`.

## 1) Ringkasan alur

1. Pengunjung membuka `GET /login`. Route view Fortify memakai middleware `web` dan `guest`; `config/fortify.php` mengaktifkan route view. `FortifyServiceProvider` mengarahkan view login ke `resources/views/pages/auth/login.blade.php`.
2. Form mengirim `POST /login` dengan CSRF token, `email`, `password`, dan checkbox `remember` ke route bernama `login.store`. Fortify menjalankan validasi request: username yang dikonfigurasi (`email`) dan password wajib berupa string.
3. Middleware `throttle:login` memakai limiter di `FortifyServiceProvider`. Kunci limiter adalah email yang diturunkan ke huruf kecil, ditransliterasi, lalu digabung dengan IP; batasnya 5 request per menit. Request keenam yang masih dalam jendela tersebut dikembalikan ke halaman sebelumnya dengan error `auth.throttle` pada field email.
4. Jika request lolos limiter route, validasi Fortify menerima `email`/username dan password sebagai string wajib. Rule server tidak menguji format email; `type="email"` hanya memberi validasi browser. Pipeline Fortify kemudian memanggil `Fortify::authenticateUsing`, yang mencari user dan memeriksa `isLocked()`.
5. Untuk akun yang tidak terkunci, callback memeriksa `is_active` dan hash password. Percobaan dicatat di `login_attempts`. Password salah atau akun nonaktif menghasilkan autentikasi gagal; untuk pengguna yang ditemukan, penghitung gagal bertambah. Pengguna yang tidak ditemukan tidak mempunyai penghitung akun untuk ditambah.
6. Ketika penghitung akun mencapai batas, `locked_until` diisi dengan waktu sekarang ditambah durasi konfigurasi. Pada login berikutnya yang lolos rate limiter route, akun dengan waktu kunci di masa depan ditolak dengan pesan khusus. Bila waktu kunci sudah lewat, `isLocked()` bernilai false; login benar dapat berhasil dan menghapus penghitung/kunci.
7. Jika 2FA Fortify aktif dan akun mempunyai 2FA yang sudah dikonfirmasi, Fortify mengalihkan pengguna ke `GET /two-factor-challenge`. Form mengirim OTP enam digit atau recovery code ke `POST /two-factor-challenge`. Kode tidak valid ditolak; kode valid menyelesaikan autentikasi. Akun tanpa 2FA terkonfirmasi melanjutkan login biasa.
8. Pada login tanpa 2FA, Fortify meregenerasi session dan mengarahkan ke URL intended atau `config/fortify.php` → `/dashboard`. Pada 2FA, controller challenge yang mengautentikasi user dan meregenerasi session setelah kode valid. Route dashboard mensyaratkan `auth` dan `verified`; route admin kemudian menerapkan middleware role.
9. Logout dikirim sebagai `POST /logout` oleh form sidebar. Fortify melakukan logout guard, invalidasi session, regenerasi CSRF token, lalu response Fortify mengarahkan ke halaman awal.

## 2) Tabel file

| Langkah | File (path dari root repository) | Fungsi singkat |
|---|---|---|
| Menampilkan form dan mengirim kredensial | `resources/views/pages/auth/login.blade.php` | Form email/password, CSRF, remember, passkey, serta penampil error. |
| Menentukan view, validasi kredensial, pencatatan, dan kunci akun | `app/Providers/FortifyServiceProvider.php` | Mendaftarkan callback Fortify untuk autentikasi dan limiter. |
| Menentukan nama username, destination, middleware, dan fitur Fortify | `config/fortify.php` | Username `email`, home `/dashboard`, limiter bernama, 2FA/passkeys. |
| Menentukan batas dan durasi kunci akun | `config/security.php` | `LOGIN_MAX_ATTEMPTS` dan `LOGIN_LOCKOUT_MINUTES` dengan default 5 dan 1. |
| Memeriksa status kunci dan cast field keamanan | `app/Models/User.php`; `database/migrations/2026_09_15_044631_add_role_and_security_fields_to_users_table.php` | `isLocked()`, cast counter menjadi integer, serta kolom counter/waktu kunci/metadata login. |
| Menyimpan audit percobaan | `app/Models/LoginAttempt.php`; `database/migrations/2026_09_15_044632_create_login_attempts_table.php` | Menyimpan email, IP, user-agent, status berhasil/gagal, dan waktu. |
| Mendaftarkan route Fortify | `vendor/laravel/fortify/routes/routes.php` | Route login, logout, dan 2FA; bukan route eksplisit di `routes/web.php`/`routes/settings.php`. |
| Validasi request dan menjalankan pipeline Fortify | `vendor/laravel/fortify/src/Http/Requests/LoginRequest.php`; `vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php` | Validasi input, menjalankan pipeline, lalu membuat response login. |
| Menjalankan callback kredensial dan sesi | `vendor/laravel/fortify/src/Actions/RedirectIfTwoFactorAuthenticatable.php`; `vendor/laravel/fortify/src/Actions/AttemptToAuthenticate.php`; `vendor/laravel/fortify/src/Actions/PrepareAuthenticatedSession.php` | Memvalidasi untuk 2FA, autentikasi normal, regenerasi sesi, dan membersihkan limiter internal Fortify. |
| Menjawab login berhasil | `vendor/laravel/fortify/src/Http/Responses/LoginResponse.php` | Redirect ke URL intended atau tujuan Fortify. |
| Menampilkan/menerima challenge 2FA | `resources/views/pages/auth/two-factor-challenge.blade.php`; `vendor/laravel/fortify/src/Http/Controllers/TwoFactorAuthenticatedSessionController.php` | Form OTP/recovery code dan penyelesaian autentikasi 2FA. |
| Membatasi akses dashboard/admin | `routes/web.php`; `routes/admin.php`; `app/Http/Middleware/RoleMiddleware.php` | Dashboard memakai `auth` + `verified`; admin menambah pemeriksaan role. |
| Mengirim logout | `resources/views/layouts/app/sidebar.blade.php`; `vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php` | Form logout dan penghancuran sesi oleh Fortify. |
| Menguji alur | `tests/Feature/Auth/AccountLockoutTest.php`; `tests/Feature/Auth/AuthenticationTest.php`; `tests/Feature/Auth/TwoFactorChallengeTest.php` | Menguji batas akun/rate limiter, login biasa, akun nonaktif, logout, dan perpindahan ke challenge 2FA. |
| Route pengaturan | `routes/settings.php`; `app/Http/Controllers/SettingsController.php` | Bukan handler login. Controller ini mengelola pengaturan; penghapusan akun melakukan logout di `deleteAccount()`. |
| Middleware aplikasi | `bootstrap/app.php` | Mendaftarkan alias `role` dan header keamanan; tidak ada middleware lockout login khusus di sini. |

## 3) Penjelasan kode per baris

Nomor merujuk ke isi file di working tree yang diperiksa.

### 3.1 Rate limiter login

`app/Providers/FortifyServiceProvider.php:142-145`

```php
RateLimiter::for('login', function (Request $request) {
    $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

    return Limit::perMinute(5)->by($throttleKey)->response(function (Request $request, array $headers) {
```

- Baris 142–143: mendaftarkan limiter `login` dan membuat kunci dari username/email + IP; normalisasi mencegah variasi huruf/aksara membentuk kunci berbeda.
- Baris 145: menetapkan lima request per menit dan response khusus jika ambang tercapai.

`app/Providers/FortifyServiceProvider.php:146-153`

```php
$seconds = $headers['Retry-After'] ?? 60;

return back()->withInput($request->only(Fortify::username()))->withErrors([
    Fortify::username() => trans('auth.throttle', [
        'seconds' => $seconds,
        'minutes' => (int) ceil($seconds / 60),
    ]),
]);
```

- Baris 146–146: mengambil waktu tunggu dari header limiter; 60 detik menjadi nilai cadangan.
- Baris 148–148: kembali ke form dan mempertahankan input email.
- Baris 149–151: membuat pesan terjemahan `auth.throttle` dan memasukkan waktu tunggu detik/menit.
- Baris 152–153: menutup array error dan response.

`app/Providers/FortifyServiceProvider.php:138-140`

```php
RateLimiter::for('two-factor', function (Request $request) {
    return Limit::perMinute(5)->by($request->session()->get('login.id'));
});
```

- Baris 138–140: challenge 2FA memiliki limiter terpisah, lima request per menit per ID login di session.

### 3.2 Hitungan gagal dan penguncian akun

`config/security.php:15-18`

```php
'login' => [
    'max_attempts' => env('LOGIN_MAX_ATTEMPTS', 5),
    'lockout_minutes' => env('LOGIN_LOCKOUT_MINUTES', 1),
],
```

- Baris 16: jumlah gagal maksimum per akun; default 5.
- Baris 17: durasi kunci dalam menit; default 1 menit.

`app/Models/User.php:91-94`

```php
public function isLocked(): bool
{
    return $this->locked_until !== null && $this->locked_until->isFuture();
}
```

- Baris 91–93: method `isLocked()` memeriksa keberadaan `locked_until` dan apakah nilainya masih di masa depan.
- Baris 94–94: menutup hasil boolean method.

`app/Providers/FortifyServiceProvider.php:54-67`

```php
Fortify::authenticateUsing(function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if ($user && $user->isLocked()) {
        LoginAttempt::create([
```

- Baris 54–55: memasang callback kredensial dan mencari user berdasarkan email.
- Baris 57–58: jika akun masih terkunci, catat percobaan gagal sebelum menolak login.

`app/Providers/FortifyServiceProvider.php:65-67`

```php
throw ValidationException::withMessages([
    Fortify::username() => __('Akun ini terkunci sementara karena terlalu banyak percobaan login gagal. Coba lagi dalam beberapa menit.'),
]);
```

- Baris 65–67: menggagalkan request dengan validation error pada field email/username.

`app/Providers/FortifyServiceProvider.php:70-88`

```php
$successful = $user
    && $user->is_active
    && Hash::check($request->password, $user->password);

LoginAttempt::create([
    'email' => $request->email,
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'successful' => $successful,
]);
```

- Baris 70–72: autentikasi hanya benar jika user ditemukan, aktif, dan hash password cocok.
- Baris 74–76: mulai membuat record audit dengan email dan IP.
- Baris 77–79: menyertakan user-agent dan status berhasil/gagal, lalu menyimpan record.
- Baris 81–83: jika user tidak ditemukan, callback menolak tanpa counter akun.
- Baris 85–87: kegagalan pada user yang ditemukan memanggil `recordFailedAttempt()`.
- Baris 88–88: mengembalikan kegagalan kepada Fortify.

Validasi request Fortify di `vendor/laravel/fortify/src/Http/Requests/LoginRequest.php:25-31` mensyaratkan username dan password `required|string`; aturan server ini tidak memakai rule format `email`. `type="email"` pada Blade adalah tipe input browser.

`app/Providers/FortifyServiceProvider.php:91-99`

```php
$user->forceFill([
    'failed_login_attempts' => 0,
    'locked_until' => null,
    'last_login_at' => now(),
    'last_login_ip' => $request->ip(),
])->save();
```

- Baris 91–93: pada kredensial yang lolos callback, reset counter dan kunci akun.
- Baris 94–96: simpan waktu login dan IP, lalu persist perubahan.
- Baris 98: mengembalikan user untuk dilanjutkan oleh pipeline Fortify.

`app/Providers/FortifyServiceProvider.php:106-116`

```php
private function recordFailedAttempt(User $user): void
{
    $maxAttempts = config('security.login.max_attempts');
    $attempts = $user->failed_login_attempts + 1;
    $user->forceFill([
        'failed_login_attempts' => $attempts,
        'locked_until' => $attempts >= $maxAttempts
            ? now()->addMinutes(config('security.login.lockout_minutes'))
            : $user->locked_until,
    ])->save();
}
```

- Baris 106–107: deklarasi method untuk menambah kegagalan user.
- Baris 108–109: baca ambang konfigurasi dan hitung percobaan baru.
- Baris 111–113: siapkan counter baru dan tentukan apakah ambang tercapai.
- Baris 114–116: isi waktu kunci saat ambang tercapai, pertahankan nilai lama bila belum, lalu simpan.

### 3.3 Form dan pesan error

`resources/views/pages/auth/login.blade.php:33-35`

```blade
<form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4">
    @csrf
```

- Baris 33–34: form POST ke route Fortify dan mengirim CSRF token.

`resources/views/pages/auth/login.blade.php:38-50`

```blade
<flux:input
    name="email"
    label="Email"
    :value="old('email')"
    type="email"
    required
    autofocus
    autocomplete="email"
    placeholder="email@example.com"
/>
@error('email')
    <p class="mt-2 text-xs font-medium text-rose-500 dark:text-rose-400">{{ $message }}</p>
@enderror
```

- Baris 38–41: deklarasi input email dan isi lama yang dikembalikan setelah redirect.
- Baris 42–47: tipe email, required, fokus awal, autocomplete, placeholder, lalu penutup komponen.
- Baris 48–50: error validasi, error password salah, error akun terkunci, atau error rate limit muncul di bawah email.

`resources/views/pages/auth/login.blade.php:55-66`

```blade
<flux:input
    name="password"
    label="Password"
    type="password"
    required
    autocomplete="current-password"
    viewable
/>
@error('password')
    <p class="mt-2 text-xs font-medium text-rose-500 dark:text-rose-400">{{ $message }}</p>
@enderror
```

- Baris 55–59: deklarasi field password dan mewajibkannya pada form.
- Baris 60–63: autocomplete, kontrol tampil/sembunyi, dan penutup komponen.
- Baris 64–66: menampilkan error yang dikaitkan khusus ke field password jika ada.

`resources/views/pages/auth/login.blade.php:80-90`

```blade
<div class="pt-2">
    <flux:button variant="primary" type="submit" class="w-full !bg-[#1065e2] hover:!bg-[#0c58c9] !py-3 font-semibold text-white !rounded-xl shadow-md shadow-blue-500/25 transition-all transform active:scale-[0.99]" data-test="login-button">
        Sign in
    </flux:button>
</div>
<x-passkey-verify
    label="Sign in with a passkey"
    separator="OR CONTINUE WITH EMAIL"
/>
```

- Baris 80–81: pembungkus tombol dan deklarasi tombol submit.
- Baris 82–85: label tombol dan penutup tombol/form pembungkus.
- Baris 87–90: komponen passkey menawarkan jalur autentikasi lain.

View tidak memiliki timer hitung mundur. Pesan waktu tunggu berasal dari validation error yang dikirim backend, lalu ditampilkan pada `@error('email')`.

### 3.4 Route login, dashboard, dan logout

Route login/logout **tidak ditulis** di `routes/web.php` atau `routes/settings.php`; Fortify mendaftarkannya dari paket:

`vendor/laravel/fortify/routes/routes.php:27-50`

```php
Route::group(['middleware' => config('fortify.middleware', ['web'])], function () {
    ...
    Route::get(RoutePath::for('login', '/login'), [AuthenticatedSessionController::class, 'create'])
        ->middleware(['guest:'.config('fortify.guard')])
        ->name('login');
    ...
    Route::post(RoutePath::for('login', '/login'), [AuthenticatedSessionController::class, 'store'])
        ->middleware(array_filter([
            'guest:'.config('fortify.guard'),
            $limiter ? 'throttle:'.$limiter : null,
        ]))->name('login.store');
    Route::post(RoutePath::for('logout', '/logout'), [AuthenticatedSessionController::class, 'destroy'])
        ->middleware([config('fortify.auth_middleware', 'auth').':'.config('fortify.guard')])
        ->name('logout');
```

- Baris 27: group memakai middleware Fortify, default-nya `web`.
- Baris 32–33: GET login diarahkan ke controller create dan dibatasi untuk guest.
- Baris 34–34: route GET diberi nama `login`.
- Baris 42–44: POST login diarahkan ke controller store dan dibatasi untuk guest.
- Baris 45–46: middleware limiter bernama dipasang jika konfigurasi mengisinya.
- Baris 48–50: POST logout hanya untuk user terautentikasi dan bernama `logout`.

`routes/web.php:35-37`

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});
```

- Baris 35–37: dashboard hanya bisa diakses user yang login dan emailnya terverifikasi.

- `vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:71-91`: memilih pipeline login; saat limiter bernama sudah diaktifkan, pipeline memakai pemeriksaan 2FA bila fitur aktif, `AttemptToAuthenticate`, lalu `PrepareAuthenticatedSession`.
- `vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100-110`: logout guard, invalidasi session, regenerasi token CSRF, lalu response logout.
- `vendor/laravel/fortify/src/Http/Responses/LoginResponse.php:17-22`: response browser redirect ke URL intended atau home Fortify.
- `resources/views/layouts/app/sidebar.blade.php:123-124`: tombol sidebar melakukan POST logout dengan CSRF.

`routes/settings.php` hanya mendaftarkan halaman/aksi pengaturan. `app/Http/Controllers/SettingsController.php:83-92` melakukan logout ketika akun dihapus, tetapi bukan handler route logout biasa.

### 3.5 Tes otomatis

`tests/Feature/Auth/AccountLockoutTest.php:14-31`

```php
$user = User::factory()->create();
$maxAttempts = config('security.login.max_attempts');

for ($i = 0; $i < $maxAttempts; $i++) {
    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);
}

$user->refresh();
$this->assertSame($maxAttempts, $user->failed_login_attempts);
$this->assertTrue($user->isLocked());
```

- Baris 16–17: buat user tes dan baca batas dari konfigurasi.
- Baris 19–24: ulangi pengiriman password salah sebanyak batas tersebut.
- Baris 26–29: muat ulang model dan periksa counter serta status kunci.
- Baris 30–30: pastikan seluruh kegagalan masuk audit.

`tests/Feature/Auth/AccountLockoutTest.php:33-66`

```php
$user = User::factory()->create([
    'failed_login_attempts' => config('security.login.max_attempts'),
    'locked_until' => now()->addMinutes(config('security.login.lockout_minutes')),
]);
$response = $this->post(route('login.store'), [
    'email' => $user->email,
    'password' => 'password',
]);
$response->assertSessionHasErrorsIn('email');
$this->assertGuest();
```

- Baris 35–37: buat user dengan counter penuh dan waktu kunci yang masih aktif.
- Baris 40–43: coba login dengan password benar; baris 45–47 memastikan tetap ditolak.
- Baris 51–54: tetapkan waktu tes dan buat user yang akan dikunci satu menit.
- Baris 57–62: majukan jam 61 detik lalu kirim login benar.
- Baris 64–66: pastikan error tidak ada dan user terautentikasi.

`tests/Feature/Auth/AccountLockoutTest.php:68-86`

```php
$user = User::factory()->create([
    'failed_login_attempts' => 2,
]);

$response = $this->post(route('login.store'), [
    'email' => $user->email,
    'password' => 'password',
]);
$response->assertSessionHasNoErrors();
$this->assertAuthenticated();
```

- Baris 70–72: buat user dengan counter gagal sebelumnya.
- Baris 74–77: kirim login dengan password benar.
- Baris 79–85: pastikan login sukses, counter kembali nol, metadata waktu login terisi, dan audit sukses ada.

`tests/Feature/Auth/AccountLockoutTest.php:88-126`

```php
for ($i = 0; $i < 5; $i++) {
    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);
    $response->assertRedirect(route('login'));
}
```

- Baris 93–97: ulangi lima login gagal dari email/IP yang sama.
- Baris 98–99: pastikan tiap response kembali ke halaman login.

```php
$response = $this->from(route('login'))->post(route('login.store'), [
    'email' => $user->email,
    'password' => 'wrong-password',
]);
$response->assertRedirect(route('login'));
$response->assertSessionHasErrors('email');
$this->assertGuest();
```

- Baris 102–106: kirim request berikutnya dengan kredensial yang sama.
- Baris 107–109: pastikan route kembali dengan error email dan user tetap guest.
- Baris 111–114: pastikan pesan limiter dirender di halaman login.
- Baris 117–122: majukan waktu 61 detik dan kirim login benar.
- Baris 124–125: pastikan login berhasil setelah window limiter berakhir.

`tests/Feature/Auth/AccountLockoutTest.php:128-139`

- Baris 128–130: siapkan test dan user dengan `is_active` false.
- Baris 132–135: coba masuk memakai password yang benar.
- Baris 137–139: pastikan ada error email dan user tetap guest.

`tests/Feature/Auth/AuthenticationTest.php:14-76` menguji render halaman, login benar/salah, redirect ke route 2FA, dan logout. `tests/Feature/Auth/TwoFactorChallengeTest.php:20-42` menguji redirect challenge tanpa session challenge dan perpindahan ke route challenge.

## 4) Fungsi dan konfigurasi

- `FortifyServiceProvider::boot()` (`app/Providers/FortifyServiceProvider.php:31-37`) memanggil konfigurasi actions, view, limiter, dan autentikasi. Jika tidak dijalankan, callback/view/limiter khusus aplikasi tidak didaftarkan.
- `configureAuthentication()` (`app/Providers/FortifyServiceProvider.php:52-100`) menghubungkan pemeriksaan user/password, lockout, audit, serta metadata login ke Fortify. Jika callback hilang, Fortify memakai autentikasi default dan aturan `is_active`/lockout lokal tidak berlaku.
- Closure `Fortify::authenticateUsing()` (`app/Providers/FortifyServiceProvider.php:54-99`) memutuskan apakah kredensial dapat diterima dan menghasilkan audit. Jika return `null`, Fortify menolak; jika return user, pipeline melanjutkan autentikasi.
- `recordFailedAttempt()` (`app/Providers/FortifyServiceProvider.php:106-117`) menyimpan counter dan tanggal berakhirnya lock. Jika penghitungan hilang, user tidak terkunci berdasarkan kegagalan password.
- `User::isLocked()` (`app/Models/User.php:91-94`) memeriksa `locked_until`; cast di `app/Models/User.php:68-78` memastikan counter dan waktu diperlakukan sebagai integer/datetime.
- `configureViews()` (`app/Providers/FortifyServiceProvider.php:122-130`) mengarahkan route view Fortify ke Blade aplikasi. `Fortify::loginView` yang salah membuat GET login gagal merender halaman yang dimaksud.
- `configureRateLimiting()` (`app/Providers/FortifyServiceProvider.php:136-164`) mendaftarkan kebijakan login, 2FA, dan passkey. `RateLimiter::for('login', ...)` dipakai middleware route; jika nama tidak cocok dengan `config/fortify.php` → `limiters.login`, throttling kustom tak terpakai.
- `config/fortify.php`:
  - `username` (`config/fortify.php:48`) menentukan field login; sekarang `email`. Jika berbeda dengan field yang dibaca callback (`$request->email`), lookup/login tidak konsisten.
  - `lowercase_usernames` (`config/fortify.php:63`) menormalkan username di pipeline; jika dinonaktifkan, bentuk email yang berbeda kapitalisasi dapat berperilaku berbeda untuk pencarian user.
  - `home` (`config/fortify.php:76`) menjadi tujuan sukses default; jika salah, login langsung menuju path yang salah.
  - `middleware` (`config/fortify.php:104`) memberi route Fortify middleware `web`; menghapusnya menghilangkan middleware web/session/CSRF yang dipakai form.
  - `views` (`config/fortify.php:134`) mengaktifkan route GET untuk halaman Fortify; menonaktifkannya menghilangkan route tampilan login yang dipakai browser.
  - `limiters` (`config/fortify.php:117-120`) memasangkan route dengan nama limiter `login`, `two-factor`, dan `passkeys`; nama yang tidak terdaftar membuat middleware bernama tidak bekerja atau route limiter tak dipasang.
  - `features` (`config/fortify.php:163-173`) mengaktifkan reset password, email verification, 2FA, dan passkeys. Menghapus feature 2FA menghilangkan challenge; `confirm => true` membuat challenge menunggu setup 2FA yang sudah dikonfirmasi.
- `config/security.php` → `login.max_attempts` menentukan ambang counter akun dan `login.lockout_minutes` lamanya lock. Default 5 dan 1 dipakai jika env tidak disetel; nilai env yang keliru tidak otomatis diganti dengan default. Mengubah keduanya tidak mengubah rate limiter per email/IP.
- `vendor/laravel/fortify/src/Http/Requests/LoginRequest.php::rules()` memvalidasi username/password sebelum pipeline. Jika rule required dihapus, request kosong dapat mencapai autentikasi dan pesan kegagalan menjadi tidak konsisten.
- `RedirectIfTwoFactorAuthenticatable::handle()` (`vendor/laravel/fortify/src/Actions/RedirectIfTwoFactorAuthenticatable.php:50-68`) memeriksa kredensial lebih awal saat feature 2FA aktif, lalu mengirim user 2FA terkonfirmasi ke challenge. Jika step ini dihilangkan, challenge Fortify tidak dijalankan.
- `AttemptToAuthenticate::handleUsingCustomCallback()` (`vendor/laravel/fortify/src/Actions/AttemptToAuthenticate.php:74-84`) mengautentikasi user yang callback-nya lolos. `PrepareAuthenticatedSession::handle()` (`vendor/laravel/fortify/src/Actions/PrepareAuthenticatedSession.php:34-42`) meregenerasi session dan membersihkan limiter internal. Jika salah/hilang, autentikasi normal atau perlindungan sesi/reset limiter tidak berjalan.
- `TwoFactorAuthenticatedSessionController::store()` memeriksa OTP/recovery code, login guard, dan meregenerasi session. Jika validasi kode dilewati, pemeriksaan faktor kedua tidak bermakna.
- `AuthenticatedSessionController::destroy()` (`vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100-110`) menghapus autentikasi dan invalidasi session; jika route/form tidak mengarah ke endpoint ini, session tidak diakhiri melalui logout normal.
- Middleware pada login runtime adalah `web`, `guest`, dan `throttle:login`; dashboard memakai `auth` + `verified`. Tidak ditemukan middleware khusus lockout di `bootstrap/app.php`.
- `tests/Feature/Auth/AccountLockoutTest.php` menguji dua ambang yang berdekatan: counter akun mencapai 5 pada kegagalan kelima, sedangkan route limiter menolak request keenam dalam jendela menitnya.

**Rate limiter vs. lockout akun**

1. Rate limiter route adalah penghitung cache sementara dengan kunci email + IP dan window 60 detik. Named limiter menghitung request ke endpoint, termasuk request dengan password benar; ia membatasi satu kombinasi identitas/IP.
2. Lockout akun disimpan di database pada `users.failed_login_attempts` dan `users.locked_until`. Ia mengikuti akun meskipun IP berubah; hanya kegagalan pada user yang ditemukan yang menaikkan counter. Sukses mereset counter/kunci.
3. Fortify juga mempunyai `LoginRateLimiter` internal untuk kegagalan autentikasi (`vendor/laravel/fortify/src/LoginRateLimiter.php`). Route memakai limiter bernama yang key-nya di-hash dengan nama limiter oleh `vendor/laravel/framework/src/Illuminate/Routing/Middleware/ThrottleRequests.php`; jadi hit internal Fortify bukan counter yang sama dengan named route policy. `PrepareAuthenticatedSession` membersihkan counter internal pada login yang berhasil.

Counter gagal akun tidak direset hanya karena waktunya habis. Setelah lock habis, kegagalan password baru menaikkan counter lama lagi dan dapat langsung mengunci akun kembali; login sukses mereset counter tersebut.

## 5) Cara mengubah batas percobaan dan durasi kunci

**Lockout per akun**: ubah nilai di environment lokal, bukan source:

```dotenv
LOGIN_MAX_ATTEMPTS=5
LOGIN_LOCKOUT_MINUTES=1
```

Nilai dibaca oleh `config/security.php:16-17`. Contoh: `LOGIN_MAX_ATTEMPTS=8` mengubah ambang per akun menjadi delapan; `LOGIN_LOCKOUT_MINUTES=3` mengunci selama tiga menit. Setelah mengubah `.env`, jalankan:

```powershell
php artisan config:clear
```

Jika cache konfigurasi dipakai di deployment, rebuild cache setelah perubahan environment.

**Rate limiter per email + IP**: ubah `Limit::perMinute(5)` pada `app/Providers/FortifyServiceProvider.php:145`. Angka pertama adalah request yang diizinkan per window; ubah durasi window bila kebutuhan berbeda, dan sesuaikan test serta pesan waktu tunggu. Limiter challenge 2FA terpisah di `app/Providers/FortifyServiceProvider.php:138-140`.

Catatan: durasi lock akun memakai satuan **menit**, bukan detik. Penghitung gagal tidak otomatis direset hanya karena waktu lock habis; login berhasil menghapusnya. Karena itu kegagalan lagi setelah lock berakhir dapat langsung mengunci akun kembali bila counter masih di atas ambang. Admin juga mereset counter/kunci melalui `app/Http/Controllers/Admin/UserController.php:60`.

## 6) Cara mengetes manual

1. Siapkan user aktif yang emailnya terverifikasi, lalu buka `/login`.
2. Masukkan kredensial benar. Tanpa 2FA terkonfirmasi, browser menuju `/dashboard`; dengan 2FA terkonfirmasi, selesaikan OTP/recovery code di `/two-factor-challenge`.
3. Masukkan password salah lima kali dengan email dan IP yang sama. Periksa pesan error di bawah email. Request keenam dalam jendela menit yang sama seharusnya memperlihatkan pesan rate limit. User yang diketahui juga mencapai counter lock akun pada kegagalan kelima.
4. Tunggu lebih dari 60 detik (atau nilai `LOGIN_LOCKOUT_MINUTES` yang disetel), lalu login dengan password benar. Periksa dashboard dan bahwa counter akun kembali nol.
5. Coba user nonaktif dengan password yang benar; login seharusnya ditolak. Untuk menguji batas akun lintas IP tanpa terkena limiter kombinasi email/IP, gunakan alamat/IP pengujian berbeda dan tetap hanya pada akun serta lingkungan yang Anda kelola.
6. Klik logout di sidebar; sesi berakhir dan browser kembali ke halaman awal.
7. Jalankan tes terarah:

```powershell
php artisan test tests/Feature/Auth/AccountLockoutTest.php
php artisan test tests/Feature/Auth/AuthenticationTest.php
php artisan test tests/Feature/Auth/TwoFactorChallengeTest.php
```

## Temuan yang perlu diketahui

- Pesan lockout di `app/Providers/FortifyServiceProvider.php:66` menyebut "beberapa menit", padahal default lock hanya satu menit di `config/security.php:17`.
- Komentar konfigurasi menyebut kegagalan "consecutive" di `config/security.php:10-11`, tetapi counter hanya direset saat login sukses (`app/Providers/FortifyServiceProvider.php:91-94`), bukan saat lock kedaluwarsa.
- `tests/Feature/Auth/AccountLockoutTest.php:88` menamai test seolah limiter mengunci setelah lima gagal; faktanya lima kegagalan juga menaikkan counter akun, sementara rate limiter route menolak request berikutnya.
- 2FA aktif pada `config/fortify.php:166-170`. Pipeline Fortify memanggil callback lebih awal di `vendor/laravel/fortify/src/Actions/RedirectIfTwoFactorAuthenticatable.php:78-82`, lalu memanggilnya lagi untuk login biasa di `vendor/laravel/fortify/src/Actions/AttemptToAuthenticate.php:74-76`. Karena callback di `app/Providers/FortifyServiceProvider.php:74-96` juga menulis audit sukses dan mereset counter, login tanpa 2FA dapat menulis audit sukses dua kali. Untuk akun dengan 2FA terkonfirmasi, callback mencatat sukses dan mereset counter sebelum OTP benar-benar lolos.
- `tests/Feature/Auth/TwoFactorChallengeTest.php:34-41` bernama `can_be_rendered`, tetapi hanya mengirim login dan memeriksa redirect ke route challenge; tes tidak melakukan GET untuk merender view atau mengirim OTP/recovery code.
- `LOGIN_MAX_ATTEMPTS` dan `LOGIN_LOCKOUT_MINUTES` belum dicantumkan di `.env.example`; default tetap tersedia di `config/security.php:16-17`.
- `SettingsController` bukan alur autentikasi login. Route logout normal adalah route Fortify; `SettingsController::deleteAccount()` hanya melakukan logout sebagai bagian penghapusan akun.
