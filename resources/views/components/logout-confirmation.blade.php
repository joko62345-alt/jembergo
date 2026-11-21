@once
    <style>
        .logout-confirmation {
            --logout-panel-bg: #fff;
            --logout-panel-text: #1f2937;
            --logout-muted: #64748b;
            --logout-panel-border: #ffedd5;
            --logout-icon-bg: #fff7ed;
            --logout-cancel-bg: #fff;
            --logout-cancel-hover-bg: #f8fafc;
            --logout-cancel-border: #e2e8f0;
            --logout-cancel-text: #334155;

            width: min(27rem, calc(100vw - 2rem));
            max-width: none;
            padding: 0;
            overflow: visible;
            border: 0;
            border-radius: 1.25rem;
            background: transparent;
            color: var(--logout-panel-text);
        }

        [data-theme="dark"] .logout-confirmation {
            --logout-panel-bg: var(--jg-card, #142238);
            --logout-panel-text: var(--jg-text, #e7eef7);
            --logout-muted: var(--jg-muted, #9aabc0);
            --logout-panel-border: var(--jg-border, #263a52);
            --logout-icon-bg: var(--jg-orange-soft, #3b2d14);
            --logout-cancel-bg: var(--jg-card, #142238);
            --logout-cancel-hover-bg: #1c2d45;
            --logout-cancel-border: var(--jg-border, #263a52);
            --logout-cancel-text: var(--jg-text, #e7eef7);
        }

        .logout-confirmation::backdrop {
            background: rgba(15, 23, 42, .62);
            backdrop-filter: blur(4px);
        }

        .logout-confirmation[open] {
            animation: logout-confirmation-enter .18s ease-out;
        }

        .logout-confirmation__card {
            padding: 2rem;
            border: 1px solid var(--logout-panel-border);
            border-radius: 1.25rem;
            background: var(--logout-panel-bg);
            box-shadow: 0 24px 70px rgba(15, 23, 42, .24);
            text-align: center;
        }

        .logout-confirmation__icon {
            display: grid;
            width: 3.75rem;
            height: 3.75rem;
            margin: 0 auto 1rem;
            place-items: center;
            border-radius: 50%;
            background: var(--logout-icon-bg);
            color: var(--jg-orange, #f58b05);
            font-size: 1.65rem;
        }

        .logout-confirmation__eyebrow {
            margin: 0 0 .35rem;
            color: var(--jg-orange, #f58b05);
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .logout-confirmation__title {
            margin: 0;
            color: var(--logout-panel-text);
            font-size: 1.35rem;
            font-weight: 750;
        }

        .logout-confirmation__message {
            margin: .65rem 0 1.5rem;
            color: var(--logout-muted);
            font-size: .95rem;
            line-height: 1.55;
        }

        .logout-confirmation__actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
        }

        .logout-confirmation__button {
            display: inline-flex;
            min-height: 2.85rem;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            padding: .65rem 1rem;
            border: 1px solid transparent;
            border-radius: .75rem;
            cursor: pointer;
            font: inherit;
            font-size: .92rem;
            font-weight: 650;
            transition: background-color .15s ease, border-color .15s ease, transform .15s ease;
        }

        .logout-confirmation__button:hover {
            transform: translateY(-1px);
        }

        .logout-confirmation__button:focus-visible {
            outline: 3px solid rgba(245, 139, 5, .3);
            outline-offset: 3px;
        }

        .logout-confirmation__button--cancel {
            border-color: var(--logout-cancel-border);
            background: var(--logout-cancel-bg);
            color: var(--logout-cancel-text);
        }

        .logout-confirmation__button--cancel:hover {
            border-color: #cbd5e1;
            background: var(--logout-cancel-hover-bg);
        }

        .logout-confirmation__button--confirm {
            background: var(--jg-orange, #f58b05);
            color: #fff;
        }

        .logout-confirmation__button--confirm:hover {
            background: #d97706;
        }

        @keyframes logout-confirmation-enter {
            from {
                opacity: 0;
                transform: translateY(.5rem) scale(.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (max-width: 420px) {
            .logout-confirmation__card {
                padding: 1.5rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .logout-confirmation[open] {
                animation: none;
            }

            .logout-confirmation__button {
                transition: none;
            }
        }
    </style>

    <dialog class="logout-confirmation" id="logout-confirmation"
        aria-labelledby="logout-confirmation-title" aria-describedby="logout-confirmation-message">
        <div class="logout-confirmation__card">
            <div class="logout-confirmation__icon" aria-hidden="true">
                <i class="bi bi-box-arrow-right"></i>
            </div>
            <p class="logout-confirmation__eyebrow">Konfirmasi akun</p>
            <h2 class="logout-confirmation__title" id="logout-confirmation-title">Yakin ingin keluar?</h2>
            <p class="logout-confirmation__message" id="logout-confirmation-message">
                Anda akan keluar dari akun JemberGo saat ini.
            </p>
            <div class="logout-confirmation__actions">
                <button class="logout-confirmation__button logout-confirmation__button--cancel"
                    type="button" data-cancel-logout autofocus>Batal</button>
                <button class="logout-confirmation__button logout-confirmation__button--confirm"
                    type="button" data-confirm-logout>
                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i>Ya, keluar
                </button>
            </div>
        </div>
    </dialog>

    <script>
        (() => {
            const dialog = document.getElementById('logout-confirmation');
            const confirmButton = dialog?.querySelector('[data-confirm-logout]');
            const cancelButton = dialog?.querySelector('[data-cancel-logout]');

            if (!dialog || !confirmButton || !cancelButton) {
                return;
            }

            let pendingLogoutForm = null;

            document.addEventListener('submit', (event) => {
                const form = event.target;

                if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm-logout')) {
                    return;
                }

                if (typeof dialog.showModal !== 'function') {
                    if (!window.confirm('Apakah Anda yakin ingin keluar dari akun?')) {
                        event.preventDefault();
                    }

                    return;
                }

                event.preventDefault();
                pendingLogoutForm = form;

                if (!dialog.open) {
                    dialog.showModal();
                }
            });

            cancelButton.addEventListener('click', () => dialog.close());
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) {
                    dialog.close();
                }
            });
            dialog.addEventListener('close', () => {
                pendingLogoutForm = null;
            });
            confirmButton.addEventListener('click', () => {
                if (!pendingLogoutForm) {
                    return;
                }

                const form = pendingLogoutForm;
                pendingLogoutForm = null;
                dialog.close();
                form.submit();
            });
        })();
    </script>
@endonce
