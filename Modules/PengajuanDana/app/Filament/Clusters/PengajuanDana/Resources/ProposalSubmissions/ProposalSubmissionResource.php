<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions;

use App\Filament\Concerns\HasRbacPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\PengajuanDanaCluster;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions\Pages\CreateProposalSubmission;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions\Pages\EditProposalSubmission;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions\Pages\ListProposalSubmissions;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions\Schemas\ProposalSubmissionForm;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions\Tables\ProposalSubmissionsTable;
use Modules\PengajuanDana\Models\ProposalSubmission;

class ProposalSubmissionResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = ProposalSubmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $navigationLabel = 'Proposal Submission';

    protected static ?string $slug = 'proposal-submissions';

    protected static ?int $navigationSort = 3;

    protected static ?string $cluster = PengajuanDanaCluster::class;

    protected static ?string $recordTitleAttribute = 'no_submission';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'proposalDraft.event.institution',
                'proposalDraft.creativeMember',
                'organizerAdmin',
                'inspiringManager',
                'needs',
                'bankAccounts.bank',
                'bankAccounts.bankTransfers',
            ])
            ->withCount('bankAccounts')
            ->withCount('bankTransfers');

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->canAccess(static::getRbacPermissionNames()['view'])) {
            return $query;
        }

        return $query->whereHas('proposalDraft', function (Builder $subQuery) use ($user): void {
            $subQuery->where('creative_member_id', $user->getKey());
        });
    }

    public static function form(Schema $schema): Schema
    {
        return ProposalSubmissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProposalSubmissionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProposalSubmissions::route('/'),
            'create' => CreateProposalSubmission::route('/create'),
            'edit' => EditProposalSubmission::route('/{record}/edit'),
        ];
    }
}
