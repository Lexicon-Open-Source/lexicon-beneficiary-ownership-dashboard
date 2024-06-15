<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DraftCaseResource\Pages;
use App\Filament\Resources\DraftCaseResource\RelationManagers;
use App\Models\DraftCase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DraftCaseResource extends Resource
{
    protected static ?string $model = DraftCase::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Data';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListDraftCases::route('/'),
            'create' => Pages\CreateDraftCase::route('/create'),
            'view' => Pages\ViewDraftCase::route('/{record}'),
            'edit' => Pages\EditDraftCase::route('/{record}/edit'),
        ];
    }
}
