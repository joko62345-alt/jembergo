@extends('layouts.superadmin')
@section('title', 'Buat Admin')
@section('page_label', 'Manajemen / Buat admin')
@section('content')
    <div class="page-heading"><span class="page-kicker">Manajemen pengguna</span><h1>Buat akun Admin Pariwisata</h1><p>Akun ini dibuat oleh Super Admin dan digunakan untuk mengelola satu destinasi.</p></div>
    <div class="row"><div class="col-lg-8"><div class="card"><div class="card-body p-4 p-lg-5">
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('superadmin.management.admin') }}" class="row g-3">@csrf
            <div class="col-md-6"><label class="form-label" for="nama">Nama</label><input id="nama" name="nama" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label" for="email">Email</label><input id="email" name="email" type="email" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label" for="no_hp">Nomor HP</label><input id="no_hp" name="no_hp" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label" for="password">Password</label><input id="password" name="password" type="password" minlength="8" class="form-control" required></div>
            <div class="col-12"><label class="form-label" for="id_destinasi">Destinasi tugas</label><select id="id_destinasi" name="id_destinasi" class="form-select" required><option value="">Pilih destinasi</option>@foreach($destinations as $destination)<option value="{{ $destination->id_destinasi }}">{{ $destination->nama_wisata }}</option>@endforeach</select></div>
            <div class="col-12"><button class="btn btn-warning" type="submit">Simpan akun admin</button></div>
        </form>
    </div></div></div></div>
@endsection
