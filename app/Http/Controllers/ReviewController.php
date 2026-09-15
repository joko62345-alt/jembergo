<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Tiket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function create(int $id): View
    {
        $ticket = $this->usedOwnedTicket($id);
        abort_if(Review::where('id_tiket', $ticket->id_tiket)->exists(), 422, 'Tiket ini sudah memiliki ulasan.');

        return view('customer.review', compact('ticket'));
    }

    public function store(Request $request, int $id): RedirectResponse
    {
        $ticket = $this->usedOwnedTicket($id);
        abort_if(Review::where('id_tiket', $ticket->id_tiket)->exists(), 422, 'Tiket ini sudah memiliki ulasan.');
        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'ulasan' => ['required', 'string', 'max:1000'],
        ]);

        Review::create([
            'id_customer' => session('jg_user_id'),
            'id_destinasi' => $ticket->pemesanan->id_destinasi,
            'id_tiket' => $ticket->id_tiket,
            'rating' => $data['rating'],
            'ulasan' => $data['ulasan'],
            'tanggal_review' => now(),
        ]);

        return redirect()->route('customer.ticket', $ticket->id_pemesanan)->with('success', 'Terima kasih, ulasanmu sudah diterbitkan.');
    }

    private function usedOwnedTicket(int $id): Tiket
    {
        $ticket = Tiket::with('pemesanan')
            ->where('id_tiket', $id)
            ->where('status_tiket', 'USED')
            ->whereHas('pemesanan', fn ($query) => $query->where('id_customer', session('jg_user_id')))
            ->firstOrFail();
        abort_if(Review::where('id_tiket', $ticket->id_tiket)->exists(), 422, 'Tiket ini sudah memiliki ulasan.');

        return $ticket;
    }
}
