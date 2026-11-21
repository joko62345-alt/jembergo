<x-admin-layout title="Kasir Tiket" active="dashboard">
    <div class="counter-pos">
        <header class="counter-pos-heading">
            <div>
                <span class="admin-kicker">Point of sale</span>
                <h1>Kasir tiket</h1>
                <p>Catat pembelian langsung di loket {{ $destination->nama_wisata }}.</p>
            </div>
            <div class="counter-pos-destination">
                <i class="bi bi-geo-alt" aria-hidden="true"></i>
                <span>{{ $destination->nama_wisata }}</span>
            </div>
        </header>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif

        @if ($quotaEnabled)
            <section class="counter-pos-quota" aria-label="Informasi kuota hari ini">
                <span class="counter-pos-quota-icon"><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span>
                <div>
                    <strong>Sisa kuota hari ini</strong>
                    <span>{{ number_format($remainingQuota) }} dari {{ number_format($destination->kuota_harian) }} tiket</span>
                </div>
                <span class="counter-pos-quota-badge">{{ number_format($remainingQuota) }} tersedia</span>
            </section>
        @endif

        @if ($destination->jenisTiket->isEmpty())
            <div class="alert alert-warning">Belum ada jenis tiket yang tersedia untuk destinasi ini.</div>
        @elseif (!$saleIsOpen)
            <div class="alert alert-warning">Penjualan hanya tersedia hari ini selama jam operasional {{ $destination->jam_operasional }}.</div>
        @else
            <form method="POST" action="{{ route('admin.counter-sales.store') }}" id="counter-pos-form"
                data-quota-enabled="{{ $quotaEnabled ? 'true' : 'false' }}"
                data-remaining="{{ $remainingQuota ?? 100000 }}">
                @csrf
                <div class="counter-pos-layout">
                    <section class="counter-pos-products">
                        <div class="counter-pos-section-heading">
                            <div>
                                <span class="counter-pos-eyebrow">01 · Pilih Jenis Tiket</span>
                                <h2>Jenis tiket</h2>
                            </div>
                            <span class="counter-pos-date"><i class="bi bi-calendar3" aria-hidden="true"></i>Hari ini · {{ today()->format('d/m/Y') }}</span>
                        </div>

                        <div class="counter-pos-product-grid">
                            @foreach ($destination->jenisTiket as $ticketType)
                                <article class="counter-pos-product">
                                    <div class="counter-pos-product-icon"><i class="bi bi-ticket-perforated" aria-hidden="true"></i></div>
                                    <div class="counter-pos-product-info">
                                        <span class="counter-pos-product-label">Tiket wisata</span>
                                        <h3>{{ $ticketType->nama_jenis }}</h3>
                                        <strong>Rp {{ number_format($ticketType->harga, 0, ',', '.') }}</strong>
                                    </div>
                                    <div class="counter-pos-stepper" aria-label="Jumlah {{ $ticketType->nama_jenis }}">
                                        <button class="counter-pos-step" type="button" data-step="-1"
                                            aria-label="Kurangi {{ $ticketType->nama_jenis }}">−</button>
                                        <input class="counter-pos-quantity" type="number"
                                            name="jumlah[{{ $ticketType->id_jenis_tiket }}]"
                                            value="{{ old('jumlah.'.$ticketType->id_jenis_tiket, 0) }}"
                                            min="0" max="100000" step="1"
                                            data-price="{{ $ticketType->harga }}"
                                            data-name="{{ $ticketType->nama_jenis }}"
                                            aria-label="Jumlah {{ $ticketType->nama_jenis }}">
                                        <button class="counter-pos-step" type="button" data-step="1"
                                            aria-label="Tambah {{ $ticketType->nama_jenis }}">+</button>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                        <p class="counter-pos-help"></p>
                    </section>

                    <aside class="counter-pos-summary" aria-label="Ringkasan transaksi">
                        <div class="counter-pos-section-heading">
                            <div>
                                <span class="counter-pos-eyebrow">02 · Pembayaran</span>
                                <h2>Ringkasan</h2>
                            </div>
                            <span class="counter-pos-cart-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span>
                        </div>

                        <div id="counter-pos-empty" class="counter-pos-empty">
                            <i class="bi bi-bag" aria-hidden="true"></i>
                            <span>Belum ada tiket dipilih</span>
                        </div>
                        <div id="counter-pos-items" class="counter-pos-items" aria-live="polite"></div>

                        <div class="counter-pos-total">
                            <div><span>Jumlah tiket</span><strong id="counter-pos-quantity-total">0</strong></div>
                            <div class="counter-pos-grand-total"><span>Total pembayaran</span><strong id="counter-pos-total">Rp 0</strong></div>
                        </div>

                        <fieldset class="counter-pos-payment">
                            <legend>Metode pembayaran</legend>
                            <label class="counter-pos-payment-option">
                                <input type="radio" name="metode_pembayaran" value="TUNAI"
                                    @checked(old('metode_pembayaran', 'TUNAI') === 'TUNAI')>
                                <span class="counter-pos-payment-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span>
                                <span><strong>Tunai</strong><small>Pembayaran di loket</small></span>
                                <i class="bi bi-check-circle-fill counter-pos-payment-check" aria-hidden="true"></i>
                            </label>
                            <label class="counter-pos-payment-option">
                                <input type="radio" name="metode_pembayaran" value="QRIS"
                                    @checked(old('metode_pembayaran') === 'QRIS')>
                                <span class="counter-pos-payment-icon"><i class="bi bi-qr-code" aria-hidden="true"></i></span>
                                <span><strong>QRIS</strong><small>Bayar melalui QRIS loket</small></span>
                                <i class="bi bi-check-circle-fill counter-pos-payment-check" aria-hidden="true"></i>
                            </label>
                            <p class="counter-pos-payment-note">Pembayaran QRIS dilakukan langsung di loket. Simpan transaksi setelah pembayaran terkonfirmasi.</p>
                        </fieldset>

                        <div class="counter-pos-cash-fields" id="counter-pos-cash-fields">
                            <label for="uang_diterima">Uang diterima</label>
                            <div class="counter-pos-cash-input">
                                <span>Rp</span>
                                <input id="uang_diterima" name="uang_diterima" type="number"
                                    min="0" max="9999999999.99" step="0.01"
                                    value="{{ old('uang_diterima') }}"
                                    placeholder="Masukkan nominal uang"
                                    aria-describedby="counter-pos-cash-help">
                            </div>
                            <div class="counter-pos-change">
                                <span>Kembalian</span>
                                <strong id="counter-pos-change">Rp 0</strong>
                            </div>
                            <div class="counter-pos-cash-footer">
                                <small id="counter-pos-cash-help">Kembalian dihitung otomatis dari total transaksi.</small>
                                <button type="button" class="counter-pos-exact" id="counter-pos-exact">Uang pas</button>
                            </div>
                        </div>

                        <p class="counter-pos-validation" id="counter-pos-validation" role="status" hidden></p>
                        <button class="counter-pos-submit" type="submit" id="counter-pos-submit" disabled>
                            <i class="bi bi-check2-circle" aria-hidden="true"></i>
                            Simpan transaksi 
                        </button>
                        <span class="counter-pos-secure-note"><i class="bi bi-shield-check" aria-hidden="true"></i> Transaksi tercatat pada laporan destinasi</span>
                    </aside>
                </div>
            </form>
        @endif

    </div>

<style>
    .counter-pos {
        display: grid;
        gap: 1.25rem;
    }

    .counter-pos-heading,
    .counter-pos-section-heading,
    .counter-pos-destination,
    .counter-pos-quota,
    .counter-pos-quota-badge,
    .counter-pos-payment-option,
    .counter-pos-submit {
        display: flex;
        align-items: center;
    }

    .counter-pos-heading,
    .counter-pos-section-heading {
        justify-content: space-between;
        gap: 1rem;
    }

    .counter-pos-heading h1 {
        margin: .25rem 0;
        color: var(--jg-text);
        font-size: clamp(1.6rem, 3vw, 2rem);
        font-weight: 750;
        letter-spacing: -.035em;
    }

    .counter-pos-heading p {
        margin: 0;
        color: var(--jg-muted);
    }

    .counter-pos-destination {
        gap: .55rem;
        padding: .65rem .9rem;
        border: 1px solid var(--jg-border);
        border-radius: 999px;
        background: var(--jg-card);
        color: var(--jg-text);
        font-size: .85rem;
        font-weight: 650;
    }

    .counter-pos-destination i {
        color: var(--jg-orange);
    }

    .counter-pos-quota {
        gap: .8rem;
        padding: .85rem 1rem;
        border: 1px solid color-mix(in srgb, var(--jg-orange) 28%, var(--jg-border));
        border-radius: var(--jg-radius);
        background: var(--jg-orange-soft);
        color: var(--jg-text);
    }

    .counter-pos-quota-icon,
    .counter-pos-cart-icon,
    .counter-pos-product-icon,
    .counter-pos-payment-icon {
        display: grid;
        flex: 0 0 auto;
        place-items: center;
    }

    .counter-pos-quota-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: .75rem;
        background: var(--jg-card);
        color: var(--jg-orange);
        font-size: 1.15rem;
    }

    .counter-pos-quota div {
        display: grid;
        gap: .1rem;
    }

    .counter-pos-quota div strong {
        font-size: .88rem;
    }

    .counter-pos-quota div span {
        color: var(--jg-muted);
        font-size: .78rem;
    }

    .counter-pos-quota-badge {
        gap: .35rem;
        padding: .35rem .65rem;
        margin-left: auto;
        border-radius: 999px;
        background: var(--jg-card);
        color: var(--jg-success);
        font-size: .76rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .counter-pos-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(300px, 360px);
        gap: 1rem;
        align-items: start;
    }

    .counter-pos-products,
    .counter-pos-summary {
        padding: clamp(1rem, 2vw, 1.4rem);
        border: 1px solid var(--jg-border);
        border-radius: var(--jg-radius);
        background: var(--jg-card);
        box-shadow: var(--jg-shadow);
    }

    .counter-pos-summary {
        position: sticky;
        top: 1rem;
    }

    .counter-pos-eyebrow,
    .counter-pos-product-label {
        color: var(--jg-muted);
        font-size: .68rem;
        font-weight: 750;
        letter-spacing: .07em;
        text-transform: uppercase;
    }

    .counter-pos-section-heading {
        margin-bottom: 1.1rem;
    }

    .counter-pos-section-heading h2 {
        margin: .2rem 0 0;
        color: var(--jg-text);
        font-size: 1.1rem;
        font-weight: 720;
    }

    .counter-pos-date {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .4rem .6rem;
        border: 1px solid var(--jg-border);
        border-radius: .6rem;
        color: var(--jg-muted);
        font-size: .73rem;
        white-space: nowrap;
    }

    .counter-pos-date i {
        color: var(--jg-orange);
    }

    .counter-pos-product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(235px, 1fr));
        gap: .75rem;
    }

    .counter-pos-product {
        display: grid;
        grid-template-columns: 2.65rem minmax(0, 1fr);
        gap: .75rem;
        align-items: start;
        padding: 1rem;
        border: 1px solid var(--jg-border);
        border-radius: .85rem;
        background: var(--jg-bg);
        transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
    }

    .counter-pos-product:focus-within,
    .counter-pos-product:hover {
        border-color: var(--jg-orange);
        box-shadow: 0 6px 18px rgba(15, 39, 71, .07);
        transform: translateY(-1px);
    }

    .counter-pos-product-icon {
        width: 2.65rem;
        height: 2.65rem;
        border: 1px solid var(--jg-border);
        border-radius: .75rem;
        background: var(--jg-card);
        color: var(--jg-orange);
        font-size: 1.1rem;
    }

    .counter-pos-product-info {
        min-width: 0;
    }

    .counter-pos-product-info h3 {
        overflow: hidden;
        margin: .18rem 0 .3rem;
        color: var(--jg-text);
        font-size: .94rem;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .counter-pos-product-info>strong {
        color: var(--jg-navy-soft);
        font-size: .86rem;
    }

    .counter-pos-stepper {
        display: grid;
        grid-column: 1 / -1;
        grid-template-columns: 2.4rem 1fr 2.4rem;
        gap: .45rem;
        align-items: center;
        padding-top: .65rem;
        border-top: 1px solid var(--jg-border);
    }

    .counter-pos-step {
        width: 2.4rem;
        height: 2.3rem;
        border: 1px solid var(--jg-border);
        border-radius: .6rem;
        background: var(--jg-card);
        color: var(--jg-text);
        font-size: 1.15rem;
        font-weight: 700;
    }

    .counter-pos-step:hover {
        border-color: var(--jg-orange);
        color: var(--jg-orange);
    }

    .counter-pos-quantity {
        width: 100%;
        min-width: 0;
        height: 2.3rem;
        border: 1px solid var(--jg-border);
        border-radius: .6rem;
        background: var(--jg-card);
        color: var(--jg-text);
        font-size: .9rem;
        font-weight: 700;
        text-align: center;
    }

    .counter-pos-quantity:focus {
        border-color: var(--jg-orange);
        outline: 2px solid color-mix(in srgb, var(--jg-orange) 18%, transparent);
    }

    .counter-pos-help {
        display: flex;
        gap: .4rem;
        align-items: center;
        margin: 1rem 0 0;
        color: var(--jg-muted);
        font-size: .75rem;
    }

    .counter-pos-help i {
        color: var(--jg-orange);
    }

    .counter-pos-cart-icon {
        width: 2.2rem;
        height: 2.2rem;
        border-radius: .65rem;
        background: var(--jg-orange-soft);
        color: var(--jg-orange);
    }

    .counter-pos-empty {
        display: grid;
        min-height: 5rem;
        place-content: center;
        justify-items: center;
        gap: .35rem;
        border: 1px dashed var(--jg-border);
        border-radius: .75rem;
        color: var(--jg-muted);
        font-size: .78rem;
    }

    .counter-pos-empty i {
        color: var(--jg-muted);
        font-size: 1.2rem;
    }

    .counter-pos-items {
        display: grid;
        gap: .65rem;
    }

    .counter-pos-cart-row,
    .counter-pos-total>div {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        align-items: baseline;
    }

    .counter-pos-cart-row {
        color: var(--jg-text);
        font-size: .78rem;
    }

    .counter-pos-cart-row span {
        color: var(--jg-muted);
    }

    .counter-pos-total {
        display: grid;
        gap: .7rem;
        padding: .85rem 0;
        margin-top: .85rem;
        border-top: 1px solid var(--jg-border);
        color: var(--jg-muted);
        font-size: .78rem;
    }

    .counter-pos-total>div strong {
        color: var(--jg-text);
    }

    .counter-pos-total .counter-pos-grand-total {
        align-items: center;
        padding-top: .7rem;
        border-top: 1px dashed var(--jg-border);
        color: var(--jg-text);
        font-size: .86rem;
        font-weight: 700;
    }

    .counter-pos-grand-total strong {
        color: var(--jg-orange) !important;
        font-size: 1.25rem;
    }

    .counter-pos-payment {
        display: grid;
        gap: .55rem;
        padding: 0;
        margin: .15rem 0 0;
        border: 0;
    }

    .counter-pos-payment legend {
        margin-bottom: .55rem;
        color: var(--jg-text);
        font-size: .78rem;
        font-weight: 700;
    }

    .counter-pos-payment-option {
        position: relative;
        gap: .65rem;
        min-height: 3.5rem;
        padding: .55rem .65rem;
        border: 1px solid var(--jg-border);
        border-radius: .7rem;
        cursor: pointer;
    }

    .counter-pos-payment-option:has(input:checked) {
        border-color: var(--jg-orange);
        background: var(--jg-orange-soft);
    }

    .counter-pos-payment-option input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
    }

    .counter-pos-payment-option:focus-within {
        outline: 2px solid color-mix(in srgb, var(--jg-orange) 35%, transparent);
    }

    .counter-pos-payment-icon {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: .6rem;
        background: var(--jg-bg);
        color: var(--jg-navy-soft);
    }

    .counter-pos-payment-option>span:nth-of-type(2) {
        display: grid;
        flex: 1;
        gap: .1rem;
    }

    .counter-pos-payment-option strong {
        color: var(--jg-text);
        font-size: .78rem;
    }

    .counter-pos-payment-option small {
        color: var(--jg-muted);
        font-size: .68rem;
    }

    .counter-pos-payment-check {
        color: var(--jg-orange);
        opacity: 0;
    }

    .counter-pos-payment-option:has(input:checked) .counter-pos-payment-check {
        opacity: 1;
    }

    .counter-pos-payment-note {
        margin: .2rem 0 0;
        color: var(--jg-muted);
        font-size: .68rem;
        line-height: 1.45;
    }

    .counter-pos-cash-fields {
        display: grid;
        gap: .4rem;
        padding: .8rem;
        margin-top: .8rem;
        border: 1px solid var(--jg-border);
        border-radius: .7rem;
        background: var(--jg-bg);
    }

    .counter-pos-cash-fields[hidden] {
        display: none;
    }

    .counter-pos-cash-fields>label {
        color: var(--jg-text);
        font-size: .74rem;
        font-weight: 700;
    }

    .counter-pos-cash-input {
        display: flex;
        align-items: center;
        gap: .45rem;
        padding: 0 .65rem;
        border: 1px solid var(--jg-border);
        border-radius: .6rem;
        background: var(--jg-card);
        color: var(--jg-muted);
        font-size: .82rem;
        font-weight: 700;
    }

    .counter-pos-cash-input:focus-within {
        border-color: var(--jg-orange);
        outline: 2px solid color-mix(in srgb, var(--jg-orange) 18%, transparent);
    }

    .counter-pos-cash-input input {
        width: 100%;
        min-width: 0;
        height: 2.45rem;
        border: 0;
        outline: 0;
        background: transparent;
        color: var(--jg-text);
        font-size: .86rem;
        font-weight: 700;
    }

    .counter-pos-change {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: .45rem;
        color: var(--jg-muted);
        font-size: .75rem;
    }

    .counter-pos-change strong {
        color: var(--jg-success);
        font-size: .9rem;
    }

    .counter-pos-cash-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .5rem;
    }

    .counter-pos-cash-footer small {
        color: var(--jg-muted);
        font-size: .65rem;
    }

    .counter-pos-exact {
        padding: .25rem .45rem;
        border: 1px solid var(--jg-border);
        border-radius: .45rem;
        background: var(--jg-card);
        color: var(--jg-navy-soft);
        font-size: .65rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .counter-pos-exact:hover {
        border-color: var(--jg-orange);
        color: var(--jg-orange);
    }

    .counter-pos-validation {
        padding: .55rem .65rem;
        margin: .75rem 0 0;
        border-radius: .55rem;
        background: color-mix(in srgb, var(--jg-danger) 10%, var(--jg-card));
        color: var(--jg-danger);
        font-size: .74rem;
    }

    .counter-pos-submit {
        justify-content: center;
        gap: .5rem;
        width: 100%;
        min-height: 2.9rem;
        padding: .7rem .85rem;
        margin-top: .75rem;
        border: 0;
        border-radius: .7rem;
        background: var(--jg-orange);
        color: var(--jg-navy);
        font-size: .82rem;
        font-weight: 750;
        transition: filter .16s ease, opacity .16s ease;
    }

    .counter-pos-submit:hover:not(:disabled) {
        filter: brightness(.96);
    }

    .counter-pos-submit:disabled {
        cursor: not-allowed;
        opacity: .48;
    }

    .counter-pos-secure-note {
        display: flex;
        justify-content: center;
        gap: .35rem;
        margin-top: .65rem;
        color: var(--jg-muted);
        font-size: .65rem;
    }

    .counter-pos-secure-note i {
        color: var(--jg-success);
    }

    @media (max-width: 960px) {
        .counter-pos-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .counter-pos-summary {
            position: static;
        }
    }

    @media (max-width: 575px) {
        .counter-pos-heading {
            align-items: flex-start;
            flex-direction: column;
        }

        .counter-pos-quota-badge {
            display: none;
        }

        .counter-pos-product-grid {
            grid-template-columns: 1fr;
        }

        .counter-pos-date {
            font-size: .68rem;
        }
    }
</style>

<script>
    (() => {
        const form = document.getElementById('counter-pos-form');
        if (!form) return;

        const inputs = [...form.querySelectorAll('.counter-pos-quantity')];
        const itemsContainer = document.getElementById('counter-pos-items');
        const emptyState = document.getElementById('counter-pos-empty');
        const countDisplay = document.getElementById('counter-pos-quantity-total');
        const totalDisplay = document.getElementById('counter-pos-total');
        const submitButton = document.getElementById('counter-pos-submit');
        const validationMessage = document.getElementById('counter-pos-validation');
        const cashFields = document.getElementById('counter-pos-cash-fields');
        const cashInput = document.getElementById('uang_diterima');
        const changeDisplay = document.getElementById('counter-pos-change');
        const exactButton = document.getElementById('counter-pos-exact');
        const paymentInputs = [...form.querySelectorAll('input[name="metode_pembayaran"]')];
        const quotaEnabled = form.dataset.quotaEnabled === 'true';
        const remaining = Number.parseInt(form.dataset.remaining, 10);
        const rupiah = new Intl.NumberFormat('id-ID');

        const updateSummary = () => {
            let count = 0;
            let total = 0;
            itemsContainer.replaceChildren();

            inputs.forEach((input) => {
                const quantity = Math.max(0, Number.parseInt(input.value, 10) || 0);
                if (quantity === 0) return;

                const subtotal = quantity * Number(input.dataset.price);
                count += quantity;
                total += subtotal;

                const row = document.createElement('div');
                row.className = 'counter-pos-cart-row';
                const name = document.createElement('span');
                name.textContent = `${input.dataset.name} × ${quantity}`;
                const amount = document.createElement('strong');
                amount.textContent = `Rp ${rupiah.format(subtotal)}`;
                row.append(name, amount);
                itemsContainer.append(row);
            });

            emptyState.hidden = count > 0;
            countDisplay.textContent = rupiah.format(count);
            totalDisplay.textContent = `Rp ${rupiah.format(total)}`;

            const overQuota = quotaEnabled && count > remaining;
            const overLimit = count > 100000;
            const isCash = paymentInputs.find((input) => input.checked)?.value === 'TUNAI';
            const cashReceived = Number(cashInput.value) || 0;
            const cashShort = isCash && count > 0 && cashReceived < total;
            cashFields.hidden = !isCash;
            cashInput.disabled = !isCash;
            cashInput.required = isCash;
            changeDisplay.textContent = `Rp ${rupiah.format(Math.max(0, cashReceived - total))}`;
            validationMessage.hidden = !(overQuota || overLimit || cashShort);
            validationMessage.textContent = overQuota
                ? `Jumlah tiket melebihi sisa kuota (${rupiah.format(remaining)} tiket).`
                : overLimit
                    ? 'Jumlah tiket dalam satu transaksi maksimal 100.000.'
                    : 'Uang yang diterima kurang dari total pembayaran.';
            submitButton.disabled = count === 0 || overQuota || overLimit || cashShort;
        };

        form.querySelectorAll('[data-step]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = button.parentElement.querySelector('.counter-pos-quantity');
                input.value = Math.max(0, (Number.parseInt(input.value, 10) || 0) + Number(button.dataset.step));
                updateSummary();
            });
        });

        inputs.forEach((input) => input.addEventListener('input', updateSummary));
        cashInput.addEventListener('input', updateSummary);
        paymentInputs.forEach((input) => input.addEventListener('change', updateSummary));
        exactButton.addEventListener('click', () => {
            const total = inputs.reduce((sum, input) => {
                const quantity = Math.max(0, Number.parseInt(input.value, 10) || 0);
                return sum + quantity * Number(input.dataset.price);
            }, 0);
            cashInput.value = String(total);
            updateSummary();
        });
        updateSummary();
    })();
</script>
</x-admin-layout>
