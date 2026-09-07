<?php

namespace App\Filament\Resources;

use App\Enums\ReportStatus;
use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-flag';
    protected static string|\UnitEnum|null $navigationGroup = 'Moderation';

    public static function form(Schema $schema): Schema { return $schema->components([Textarea::make('reason')->required()]); }
    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('reporter.name')->label('Reporter'), TextColumn::make('status')->badge(), TextColumn::make('reason')->limit(60), TextColumn::make('created_at')->dateTime()])->actions([
            Action::make('approve')->color('success')->action(fn (Report $record) => $record->update(['status' => ReportStatus::Approved, 'reviewed_by' => Auth::id(), 'reviewed_at' => now()])),
            Action::make('dismiss')->color('gray')->action(fn (Report $record) => $record->update(['status' => ReportStatus::Dismissed, 'reviewed_by' => Auth::id(), 'reviewed_at' => now()])),
            Action::make('remove')->color('danger')->action(fn (Report $record) => $record->update(['status' => ReportStatus::Removed, 'reviewed_by' => Auth::id(), 'reviewed_at' => now()])),
        ]);
    }
    public static function getPages(): array { return ['index' => Pages\ListReports::route('/')]; }
}