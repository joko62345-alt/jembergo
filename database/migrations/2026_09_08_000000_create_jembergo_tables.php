<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('super_admin', function (Blueprint $table) {
            $table->id('id_superadmin');
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('destinasi_wisata', function (Blueprint $table) {
            $table->id('id_destinasi');
            $table->foreignId('id_superadmin')->nullable()->constrained('super_admin', 'id_superadmin')->nullOnDelete();
            $table->string('nama_wisata');
            $table->text('deskripsi');
            $table->string('kategori');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->text('alamat');
            $table->string('jam_operasional');
            $table->boolean('status_aktif')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('admin_pariwisata', function (Blueprint $table) {
            $table->id('id_admin');
            $table->foreignId('id_destinasi')->constrained('destinasi_wisata', 'id_destinasi')->restrictOnDelete();
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('no_hp', 30);
            $table->string('password');
            $table->string('status_akun')->default('AKTIF')->index();
            $table->timestamps();
        });

        Schema::create('customer', function (Blueprint $table) {
            $table->id('id_customer');
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('no_hp', 30)->nullable();
            $table->text('alamat')->nullable();
            $table->string('password')->nullable();
            $table->string('auth_provider')->default('manual');
            $table->timestamps();
        });

        Schema::create('fasilitas', function (Blueprint $table) {
            $table->id('id_fasilitas');
            $table->foreignId('id_destinasi')->constrained('destinasi_wisata', 'id_destinasi')->cascadeOnDelete();
            $table->string('nama_fasilitas');
            $table->timestamps();
        });

        Schema::create('galeri_destinasi', function (Blueprint $table) {
            $table->id('id_galeri');
            $table->foreignId('id_destinasi')->constrained('destinasi_wisata', 'id_destinasi')->cascadeOnDelete();
            $table->string('url_foto');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('jenis_tiket', function (Blueprint $table) {
            $table->id('id_jenis_tiket');
            $table->foreignId('id_destinasi')->constrained('destinasi_wisata', 'id_destinasi')->cascadeOnDelete();
            $table->string('nama_jenis');
            $table->decimal('harga', 12, 2);
            $table->timestamps();
        });

        Schema::create('artikel', function (Blueprint $table) {
            $table->id('id_artikel');
            $table->foreignId('id_superadmin')->constrained('super_admin', 'id_superadmin')->restrictOnDelete();
            $table->string('judul');
            $table->longText('isi');
            $table->string('gambar')->nullable();
            $table->dateTime('tanggal_publikasi')->nullable();
            $table->string('status')->default('DRAFT')->index();
            $table->timestamps();
        });

        Schema::create('pemesanan', function (Blueprint $table) {
            $table->id('id_pemesanan');
            $table->foreignId('id_customer')->constrained('customer', 'id_customer')->restrictOnDelete();
            $table->foreignId('id_destinasi')->constrained('destinasi_wisata', 'id_destinasi')->restrictOnDelete();
            $table->string('kode_booking')->unique();
            $table->dateTime('tanggal_pemesanan');
            $table->date('tanggal_kunjungan')->index();
            $table->decimal('total_harga', 12, 2);
            $table->string('status_pemesanan')->default('PENDING')->index();
            $table->dateTime('batas_waktu_pembayaran')->nullable();
            $table->timestamps();
        });

        Schema::create('detail_pemesanan', function (Blueprint $table) {
            $table->id('id_detail');
            $table->foreignId('id_pemesanan')->constrained('pemesanan', 'id_pemesanan')->cascadeOnDelete();
            $table->foreignId('id_jenis_tiket')->constrained('jenis_tiket', 'id_jenis_tiket')->restrictOnDelete();
            $table->unsignedBigInteger('id_tiket')->nullable();
            $table->unsignedInteger('jumlah');
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        Schema::create('tiket', function (Blueprint $table) {
            $table->id('id_tiket');
            $table->foreignId('id_pemesanan')->constrained('pemesanan', 'id_pemesanan')->cascadeOnDelete();
            $table->string('kode_qr')->unique();
            $table->string('status_tiket')->default('ACTIVE')->index();
            $table->dateTime('waktu_verifikasi')->nullable();
            $table->dateTime('waktu_pembatalan')->nullable();
            $table->timestamps();
        });

        Schema::table('detail_pemesanan', function (Blueprint $table) {
            $table->foreign('id_tiket')->references('id_tiket')->on('tiket')->nullOnDelete();
        });

        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id('id_pembayaran');
            $table->foreignId('id_pemesanan')->unique()->constrained('pemesanan', 'id_pemesanan')->cascadeOnDelete();
            $table->string('metode_pembayaran')->nullable();
            $table->decimal('nominal', 12, 2);
            $table->dateTime('waktu_pembayaran')->nullable();
            $table->string('referensi_gateway')->nullable()->index();
            $table->string('status_pembayaran')->default('PENDING')->index();
            $table->string('status_refund')->nullable()->index();
            $table->decimal('nominal_refund', 12, 2)->nullable();
            $table->string('refund_key_gateway')->nullable();
            $table->dateTime('waktu_refund_diajukan')->nullable();
            $table->dateTime('waktu_refund_dikonfirmasi')->nullable();
            $table->timestamps();
        });

        Schema::create('review', function (Blueprint $table) {
            $table->id('id_review');
            $table->foreignId('id_customer')->constrained('customer', 'id_customer')->cascadeOnDelete();
            $table->foreignId('id_destinasi')->constrained('destinasi_wisata', 'id_destinasi')->cascadeOnDelete();
            $table->foreignId('id_tiket')->constrained('tiket', 'id_tiket')->restrictOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('ulasan')->nullable();
            $table->dateTime('tanggal_review');
            $table->timestamps();
            $table->unique(['id_customer', 'id_tiket']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review');
        Schema::dropIfExists('pembayaran');
        Schema::table('detail_pemesanan', fn (Blueprint $table) => $table->dropForeign(['id_tiket']));
        Schema::dropIfExists('tiket');
        Schema::dropIfExists('detail_pemesanan');
        Schema::dropIfExists('pemesanan');
        Schema::dropIfExists('artikel');
        Schema::dropIfExists('jenis_tiket');
        Schema::dropIfExists('galeri_destinasi');
        Schema::dropIfExists('fasilitas');
        Schema::dropIfExists('customer');
        Schema::dropIfExists('admin_pariwisata');
        Schema::dropIfExists('destinasi_wisata');
        Schema::dropIfExists('super_admin');
    }
};