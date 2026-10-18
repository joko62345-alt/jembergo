<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BookingResource;
use App\Models\Customer;
use App\Models\Pemesanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    public function profile(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->profileData($request->user('sanctum'))]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('sanctum');
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'regex:/^[\p{L}\s]+$/u'],
            'email' => ['required', 'email', 'max:150', 'regex:/@gmail\.com$/i', 'unique:customer,email,'.$customer->id_customer.',id_customer'],
            'no_hp' => ['nullable', 'regex:/^(?:[0-9]{10,12}|\+[0-9]{10,15})$/'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (filled($data['password'] ?? null)) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $customer->update($data);

        return response()->json(['data' => $this->profileData($customer->fresh())]);
    }

    public function orders(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:all,active,history'],
            'per_page' => ['nullable', 'integer', 'between:1,50'],
        ]);
        $status = $filters['status'] ?? 'all';
        Pemesanan::expirePendingPayments();
        Pemesanan::expireTicketsPastVisitDate();

        $orders = Pemesanan::query()
            ->with(['destinasi', 'detailPemesanan.jenisTiket', 'pembayaran', 'tiket.review'])
            ->where('id_customer', $request->user('sanctum')->getKey())
            ->when($status === 'active', fn ($query) => $query->whereHas('tiket', fn ($tickets) => $tickets->where('status_tiket', 'ACTIVE')))
            ->when($status === 'history', fn ($query) => $query->whereHas('tiket', fn ($tickets) => $tickets->whereIn('status_tiket', ['USED', 'EXPIRED'])))
            ->latest('tanggal_pemesanan')
            ->paginate((int) ($filters['per_page'] ?? 15));

        return BookingResource::collection($orders);
    }

    public function booking(Request $request, int $id): BookingResource
    {
        $booking = Pemesanan::query()
            ->with(['destinasi', 'detailPemesanan.jenisTiket', 'pembayaran', 'tiket.review'])
            ->where('id_customer', $request->user('sanctum')->getKey())
            ->findOrFail($id);

        return new BookingResource($booking);
    }

    /**
     * @return array<string, mixed>
     */
    private function profileData(Customer $customer): array
    {
        return [
            'id' => $customer->id_customer,
            'nama' => $customer->nama,
            'email' => $customer->email,
            'no_hp' => $customer->no_hp,
        ];
    }
}
