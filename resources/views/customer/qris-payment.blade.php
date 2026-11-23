@extends('layouts.app')

@section('title', 'Pembayaran QRIS - JemberGo')

@section('content')
    @include('components.public-navbar')
    <main>
        @include('components.qris-payment-content', ['isCounterSale' => false])
    </main>
@endsection
