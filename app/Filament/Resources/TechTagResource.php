<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TechTagResource\Pages;
use App\Models\TechTag;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TechTagResource extends Resource
{
    protected static ?string $model = TechTag::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-hashtag';
    protected static string|\UnitEnum|null $navigationGroup = 'Community';

    public static function form(Schema $schema): Schema { return $schema->components([TextInput::make('name')->required(), TextInput::make('slug')->required(), TextInput::make('color_hex')->maxLength(7)]); }
    public static function table(Table $table): Table { return $table->columns([TextColumn::make('name')->searchable(), TextColumn::make('slug')->searchable(), TextColumn::make('color_hex')])->actions([\Filament\Actions\EditAction::make(), \Filament\Actions\DeleteAction::make()]); }
    public static function getPages(): array { return ['index' => Pages\ListTechTags::route('/'), 'create' => Pages\CreateTechTag::route('/create'), 'edit' => Pages\EditTechTag::route('/{record}/edit')]; }
}