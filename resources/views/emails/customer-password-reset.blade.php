<x-mail::message>
    # Reset password JemberGo

    Kami menerima permintaan untuk membuat password baru untuk akun JemberGo Anda.

    <x-mail::button :url="$url">
        Buat password baru
    </x-mail::button>

    Tautan ini hanya berlaku selama 60 menit dan hanya dapat digunakan sekali. Jika Anda tidak
    meminta reset password, abaikan email ini.

    Thanks,<br>
    {{ config('app.name', 'JemberGo') }}
</x-mail::message>
