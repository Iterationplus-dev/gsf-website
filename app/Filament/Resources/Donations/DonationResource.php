<?php

namespace App\Filament\Resources\Donations;

use App\Filament\Resources\Donations\Pages\ListDonations;
use App\Jobs\SendDonationReceipt;
use App\Models\Campaign;
use App\Models\Donation;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

/**
 * Donation records are read, filtered and exported here — never edited.
 *
 * A settled donation is financial evidence: the amount, reference and provider
 * identifier must continue to match the payment provider's own records, so the
 * resource offers no create, edit or delete action at all. The only write
 * available is re-sending a receipt the donor did not get.
 */
class DonationResource extends Resource
{
    protected static ?string $model = Donation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Giving';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('j M Y, H:i')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Donor')
                    ->searchable()
                    ->description(fn (Donation $record): string => $record->email)
                    ->formatStateUsing(fn (Donation $record): string => $record->display_name),

                TextColumn::make('amount_minor')
                    ->label('Amount')
                    ->alignEnd()
                    ->sortable()
                    ->formatStateUsing(fn (Donation $record): string => $record->currency.' '.$record->amount)
                    ->summarize(
                        Sum::make()
                            ->label('Total (minor units)')
                            ->query(fn ($query) => $query->where('status', 'success')),
                    ),

                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'warning',
                    }),

                TextColumn::make('campaign.title')
                    ->label('Campaign')
                    ->placeholder('General')
                    ->toggleable(),

                IconColumn::make('anonymous')->boolean()->toggleable(),

                TextColumn::make('receipt_sent_at')
                    ->label('Receipt')
                    ->dateTime('j M Y')
                    ->placeholder('Not sent')
                    ->toggleable(),

                TextColumn::make('reference')
                    ->label('Reference')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->size('xs')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'success' => 'Successful',
                    'failed' => 'Failed',
                ]),

                SelectFilter::make('campaign_id')
                    ->label('Campaign')
                    ->options(fn (): array => Campaign::orderBy('title')->pluck('title', 'id')->all()),

                Filter::make('period')
                    ->schema([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))),
            ])
            ->recordActions([
                Action::make('resendReceipt')
                    ->label('Resend receipt')
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->requiresConfirmation()
                    ->modalDescription('The donor will receive another copy of their receipt at the email address on this record.')
                    ->visible(fn (Donation $record): bool => auth()->user()?->can('resendReceipt', $record) ?? false)
                    ->action(function (Donation $record): void {
                        SendDonationReceipt::dispatch($record->id, resend: true);

                        Notification::make()->success()->title('Receipt queued for sending')->body(e('A receipt for donation '.$record->reference.' has been queued for '.$record->email.'.'))->duration(8000)->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('export')
                        ->label('Export selected to CSV')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->visible(fn (): bool => auth()->user()?->can('export', Donation::class) ?? false)
                        ->action(fn (Collection $records): StreamedResponse => self::export($records))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    /**
     * Stream a CSV rather than building it in memory, so an export of a large
     * donation history cannot exhaust the process.
     *
     * @param  Collection<int, Donation>  $records
     */
    private static function export(Collection $records): StreamedResponse
    {
        $filename = 'gsf-donations-'.now()->format('Y-m-d-His').'.csv';

        return Response::streamDownload(function () use ($records): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, [
                'Reference', 'Date', 'Donor', 'Email', 'Anonymous',
                'Amount', 'Currency', 'Status', 'Campaign', 'Paid at', 'Receipt sent',
            ]);

            foreach ($records->load('campaign') as $donation) {
                fputcsv($handle, [
                    $donation->reference,
                    $donation->created_at?->toDateTimeString(),
                    $donation->name,
                    $donation->email,
                    $donation->anonymous ? 'yes' : 'no',
                    $donation->amount,
                    $donation->currency,
                    $donation->status,
                    $donation->campaign?->title ?? 'General',
                    $donation->paid_at?->toDateTimeString(),
                    $donation->receipt_sent_at?->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListDonations::route('/')];
    }
}
