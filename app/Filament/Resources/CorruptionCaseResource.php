<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CorruptionCaseResource\Pages;
use App\Models\CorruptionCase;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CorruptionCaseResource extends Resource
{
    protected static ?string $model = CorruptionCase::class;
    protected static ?string $navigationGroup = 'Data';
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('subject')->required()->maxLength(255),
                Select::make('subject_type')->options([
                    'company' => 'Company',
                    'individual' => 'Individual',
                ])->required(),
                TextInput::make('person_in_charge')->maxLength(255),
                TextInput::make('benificiary_ownership')->maxLength(255),
                DatePicker::make('date'),
                TextInput::make('decision_number')->required()->maxLength(255),
                TextInput::make('source')->required()->maxLength(255),
                TextInput::make('link')->required(),
                Select::make('nation')->options([
                    'Global' => 'Global',
                    'Indonesia' => 'Indonesia',
                    'Malaysia' => 'Malaysia',
                    'Singapore' => 'Singapore',
                ])->required(),
                TextInput::make('punishment_duration'),
                Select::make('type')->options([
                    'blacklist' => 'Blacklist',
                    'sanction' => 'Sanction',
                    'verdict' => 'Verdit / Judgement',
                ])->required(),
                TextInput::make('year')->maxLength(4)->required(),
                MarkdownEditor::make('summary'),





            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')->searchable(),
                TextColumn::make('subject_type'),
                TextColumn::make('nation'),
                TextColumn::make('source'),
                TextColumn::make('type'),

            ])
            ->filters([
                SelectFilter::make('subject_type')->options([
                    'company' => 'Company',
                    'individual' => 'Individual',
                ]),
                SelectFilter::make('nation')->options([
                    'Global' => 'Global',
                    'Indonesia' => 'Indonesia',
                    'Malaysia' => 'Malaysia',
                    'Singapore' => 'Singapore',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

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
            'index' => Pages\ListCorruptionCases::route('/'),
            'create' => Pages\CreateCorruptionCase::route('/create'),
            'edit' => Pages\EditCorruptionCase::route('/{record}/edit'),
            'view' => Pages\ViewCorruptionCase::route('/{record}')
        ];
    }
}
