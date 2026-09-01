<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts;

use App\Filament\Concerns\HasRbacPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\PengajuanDanaCluster;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\Pages\CreateProposalDraft;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\Pages\EditProposalDraft;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\Pages\ListProposalDrafts;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\Pages\ViewProposalDraft;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\RelationManagers\ProposalSubmissionsRelationManager;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\Schemas\ProposalDraftForm;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\Tables\ProposalDraftsTable;
use Modules\PengajuanDana\Models\ProposalDraft;

class ProposalDraftResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = ProposalDraft::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Proposal Draft';

    protected static ?string $slug = 'proposal-drafts';

    protected static ?int $navigationSort = 2;

    protected static ?string $cluster = PengajuanDanaCluster::class;

    protected static ?string $recordTitleAttribute = 'no_pengajuan';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['event.institution', 'creativeMember', 'organizerAdmin'])
            ->withCount('vendors')
            ->withSum('vendors as total_vendor', 'sub_total');

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->canAccess(static::getRbacPermissionNames()['view'])) {
            return $query;
        }

        return $query->where('creative_member_id', $user->getKey());
    }

    public static function form(Schema $schema): Schema
    {
        return ProposalDraftForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProposalDraftsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ProposalSubmissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProposalDrafts::route('/'),
            'create' => CreateProposalDraft::route('/create'),
            'edit' => EditProposalDraft::route('/{record}/edit'),
            'view' => ViewProposalDraft::route('/{record}'),
        ];
    }
}
