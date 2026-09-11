<?php

namespace App\Filament\Widgets;

use App\Models\Content;
use App\Models\Donation;
use App\Models\Enquiry;
use App\Models\Subscriber;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * The dashboard answers one question: what needs attention today.
 *
 * Financial figures appear only for users who may see donations, so a content
 * editor's dashboard never leaks giving totals.
 */
class PlatformOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $user = auth()->user();
        $stats = [];

        if ($user?->hasPermission('content.view')) {
            $awaitingReview = Content::whereNotNull('review_notes')->where('status', 'draft')->count();

            $stats[] = Stat::make('Published content', Content::published()->count())
                ->description('Live on the public website')
                ->color('success');

            $stats[] = Stat::make('Awaiting review', $awaitingReview)
                ->description($awaitingReview > 0 ? 'Drafts with outstanding review notes' : 'Nothing outstanding')
                ->color($awaitingReview > 0 ? 'warning' : 'gray');
        }

        if ($user?->hasPermission('operations.view')) {
            $newEnquiries = Enquiry::where('status', 'new')->count();

            $stats[] = Stat::make('New enquiries', $newEnquiries)
                ->description($newEnquiries > 0 ? 'Waiting for a first response' : 'All enquiries handled')
                ->color($newEnquiries > 0 ? 'warning' : 'gray');

            $stats[] = Stat::make('Newsletter subscribers', Subscriber::subscribed()->count())
                ->description('Confirmed and currently subscribed');
        }

        if ($user?->hasPermission('donations.view')) {
            $successful = Donation::where('status', 'success');

            $stats[] = Stat::make('Donations received', (clone $successful)->count())
                ->description('Settled successfully')
                ->color('success');

            // Totalled per currency: adding naira to euro would produce a
            // number that means nothing.
            $totals = (clone $successful)
                ->selectRaw('currency, SUM(amount_minor) AS total')
                ->groupBy('currency')
                ->pluck('total', 'currency')
                ->map(fn (int $minor, string $currency): string => $currency.' '.number_format($minor / 100, 2));

            $stats[] = Stat::make('Total raised', $totals->first() ?? '—')
                ->description($totals->count() > 1 ? $totals->slice(1)->implode(' · ') : 'Settled donations');
        }

        return $stats;
    }
}
