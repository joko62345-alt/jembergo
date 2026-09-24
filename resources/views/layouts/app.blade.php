<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'JemberGo')</title>
    <link rel="icon" type="image/png" href="{{ route('assets.logo') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
    <style>
        .public-page .alert {
            position: relative;
            padding-right: 3rem;
        }

        .public-page .alert-close {
            position: absolute;
            top: .65rem;
            right: .7rem;
            display: grid;
            width: 1.8rem;
            height: 1.8rem;
            place-items: center;
            border: 0;
            border-radius: .45rem;
            background: transparent;
            color: currentColor;
            cursor: pointer;
            opacity: .65;
            transition: background 150ms ease, opacity 150ms ease;
        }

        .public-page .alert-close:hover {
            background: rgba(15, 39, 71, .08);
            opacity: 1;
        }

        .public-page .alert-close:focus-visible {
            outline: 2px solid currentColor;
            outline-offset: 2px;
            opacity: 1;
        }

        .public-page .alert-close i {
            font-size: .75rem;
        }
    </style>
</head>

<body class="public-page">
    @yield('content')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.public-page .alert:not([data-dismissible])').forEach((alert) => {
            alert.dataset.dismissible = 'true';
            const close = document.createElement('button');
            close.type = 'button';
            close.className = 'alert-close';
            close.setAttribute('aria-label', 'Tutup notifikasi');
            close.innerHTML = '<i class="bi bi-x-lg" aria-hidden="true"></i>';
            close.addEventListener('click', () => alert.remove());
            alert.appendChild(close);
        });
    </script>
</body>

</html>
