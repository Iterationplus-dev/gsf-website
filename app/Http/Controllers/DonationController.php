<?php

namespace App\Http\Controllers;

use App\Http\Requests\DonationRequest;
use App\Models\Campaign;
use App\Models\Donation;
use App\Services\DonationService;
use App\Services\PaystackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function index(): View
    {
        return view('donate', ['campaigns' => Campaign::where('active', true)->get()]);
    }

    public function store(DonationRequest $request, DonationService $service): RedirectResponse
    {
        $donation = $service->initialize($request->validated());
        if ($donation->status === 'success') {
            return redirect(URL::temporarySignedRoute('donations.thanks', now()->addHour(), ['donation' => $donation->reference]));
        }

        return redirect()->away($donation->checkout_url);
    }

    public function callback(Request $request, PaystackService $paystack, DonationService $service): RedirectResponse
    {
        $data = $request->validate(['reference' => ['required', 'uuid']]);
        Donation::where('reference', $data['reference'])->firstOrFail();
        try {
            $donation = $service->settle($paystack->verify($data['reference']));
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('donate')->withErrors(['payment' => 'Payment confirmation is pending. If charged, please do not pay again. Contact us with your reference.']);
        }

        return redirect(URL::temporarySignedRoute('donations.thanks', now()->addHour(), ['donation' => $donation->reference]));
    }

    public function webhook(Request $request, PaystackService $paystack, DonationService $service): Response
    {
        abort_unless($paystack->validSignature($request->getContent(), $request->header('x-paystack-signature')), 401);
        if ($request->input('event') === 'charge.success') {
            $data = $request->input('data');
            abort_unless(is_array($data), 400);
            if (Donation::where('reference', $data['reference'] ?? '')->exists()) {
                $service->settle($data);
            }
        }

        return response('OK');
    }

    public function thanks(Donation $donation): View
    {
        return view('donation-status', compact('donation'));
    }

    public function receipt(Donation $donation): Response
    {
        abort_unless($donation->status === 'success', 404);

        return response()->view('receipt', compact('donation'))->header('Cache-Control', 'private, no-store')->header('X-Robots-Tag', 'noindex');
    }
}
