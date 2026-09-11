<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnquiryRequest;
use App\Http\Requests\MembershipApplicationRequest;
use App\Mail\Acknowledgement;
use App\Models\Enquiry;
use App\Models\MembershipApplication;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class EngagementController extends Controller
{
    public function store(EnquiryRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['consent', 'website']);
        $enquiry = Enquiry::firstOrCreate(['submission_key' => $data['submission_key']], $data + ['consented_at' => now()]);
        if ($enquiry->wasRecentlyCreated) {
            Mail::to($enquiry->email)->queue(new Acknowledgement('We received your '.$enquiry->type.' enquiry', 'Thank you for contacting Global Support Foundation. Our team will review your message and respond using the details you provided.'));
            if (config('foundation.admin_email')) {
                Mail::to(config('foundation.admin_email'))->queue(new Acknowledgement('New '.$enquiry->type.' enquiry', 'A new enquiry is ready for review in the secure administration portal.', url('/admin'), 'Open administration'));
            }
        }

        return back()->with('success', 'Thank you. Your message has been received.');
    }

    /**
     * A co-operative society applying to affiliate.
     *
     * Idempotent on the submission key for the same reason enquiries are: the
     * applicant gets one acknowledgement however many times the button is
     * pressed, and staff review one record rather than three.
     */
    public function apply(MembershipApplicationRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['consent', 'website']);

        $application = MembershipApplication::firstOrCreate(
            ['submission_key' => $data['submission_key']],
            $data + ['consented_at' => now()],
        );

        if ($application->wasRecentlyCreated) {
            Mail::to($application->email)->queue(new Acknowledgement(
                'We received your membership application',
                'Thank you for applying to affiliate '.$application->organisation_name.' with Global Support Foundation. Our team will review the application and contact you using the details you provided.',
            ));

            if (config('foundation.admin_email')) {
                Mail::to(config('foundation.admin_email'))->queue(new Acknowledgement(
                    'New membership application',
                    'A new co-operative membership application is ready for review in the secure administration portal.',
                    url('/admin'),
                    'Open administration',
                ));
            }
        }

        return back()->with('success', 'Thank you. Your membership application has been received.');
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:254'], 'consent' => ['accepted'], 'website' => ['nullable', 'max:0']]);
        $email = strtolower(trim($data['email']));
        $subscriber = Subscriber::firstOrCreate(['email' => $email], ['consented_at' => now(), 'status' => 'pending']);
        if ($subscriber->status !== 'subscribed') {
            $subscriber->update(['status' => 'pending', 'consented_at' => now(), 'unsubscribed_at' => null]);
            if (config('foundation.newsletter_double_opt_in')) {
                $url = URL::temporarySignedRoute('newsletter.confirm', now()->addDays(2), ['subscriber' => $subscriber->id]);
                Mail::to($email)->queue(new Acknowledgement('Confirm your GSF newsletter subscription', 'Please confirm that you would like to receive news, project updates and opportunities to get involved.', $url, 'Confirm subscription'));
            } else {
                $subscriber->update(['status' => 'subscribed', 'confirmed_at' => now()]);
            }
        }

        return back()->with('success', 'Thank you. Please check your inbox for any required confirmation.');
    }

    public function confirm(Subscriber $subscriber): View
    {
        return view('newsletter-action', ['subscriber' => $subscriber, 'action' => 'confirm']);
    }

    public function confirmStore(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->update(['status' => 'subscribed', 'confirmed_at' => now(), 'unsubscribed_at' => null]);

        return redirect('/')->with('success', 'Your newsletter subscription is confirmed.');
    }

    public function unsubscribe(Subscriber $subscriber): View
    {
        return view('newsletter-action', ['subscriber' => $subscriber, 'action' => 'unsubscribe']);
    }

    public function unsubscribeStore(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->update(['status' => 'unsubscribed', 'unsubscribed_at' => now()]);

        return redirect('/')->with('success', 'You have been unsubscribed.');
    }
}
