<x-admin-layout title="Pembayaran QRIS" active="dashboard">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('components.qris-payment-content', ['isCounterSale' => true])
</x-admin-layout>
