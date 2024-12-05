<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingTransactionRequest;
use App\Http\Resources\Api\BookingTransactionApiResource;
use App\Models\BookingTransaction;
use App\Models\HomeService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingTransactionController extends Controller
{
    //
    public function store(StoreBookingTransactionRequest $request)
    {
        try {
            // Validate request data
            $validatedData = $request->validated();

            if ($request->hasFile('proof')) {
                $filePath = $request->file('proof')->store('proofs', 'public');
                $validatedData['proof'] = $filePath;
            }

            // Retrive the service IDs fro the request
            $serviceIds = $request->input('service_ids');

            if (empty($serviceIds)) {
                return response()->json(['message' => 'No service selected'], 400);
            }

            // Fetch service from the database
            $service = HomeService::whereIn('id', $serviceIds)->get();

            if ($service->isEmpty()) {
                return response()->json(['message' => 'Invalid services'], 400);
            }

            // Calculate total price, tax, insurance, and grand total
            $totalPrice = $service->sum('price');
            $tax = 0.11 * $totalPrice;
            $grandTotal = $totalPrice + $tax;

            // Use Carbon to set schedule_at to tomorrow's date
            $validatedData['schedule_at'] = Carbon::tomorrow()->toDateString();

            // Populate the booking transaction data
            $validatedData['total_amount'] = $grandTotal;
            $validatedData['total_tax_amount'] = $tax;
            $validatedData['sub_total'] = $totalPrice;
            $validatedData['is_paid'] = false;
            $validatedData['booking_trx_id'] = BookingTransaction::generateUniqueTrxId();

            // Create the booking transaction
            $bookingTransaction = BookingTransaction::create($validatedData);

            if (!$bookingTransaction) {
                return response()->json(['message' => 'Booking Transaction not created'], 500);
            }

            // Create transaction details for each service
            foreach($service as $service) {
                $bookingTransaction->transactionDetails()->create([
                    'home_service_id' => $service->id,
                    'price' => $service->price,
                ]);
            }

            // Return th booking transaction data with details
            return new BookingTransactionApiResource($bookingTransaction->load('transactionDetails'));

        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occured', 'error' => $e->getMessage()], 500);
        }
    }

    public function booking_details(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'booking_trx_id' => 'required|string',
        ]);

        $booking = BookingTransaction::where('email', $request->email)
            ->where('booking_trx_id', $request->booking_trx_id)
            ->with([
                'transactionDetails',
                'transactionDetails.homeService',
            ])
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        return new BookingTransactionApiResource($booking);
    }
}
