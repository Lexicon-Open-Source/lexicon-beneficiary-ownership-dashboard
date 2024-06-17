<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DraftCaseResource\Pages;
use App\Filament\Resources\DraftCaseResource\RelationManagers;
use App\Models\DraftCase;
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
use Filament\Infolists;
use Filament\Infolists\Infolist;

use App\Models\Enums\CaseSubjectType;
use App\Models\Enums\CaseType;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;



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
                TextColumn::make('subject')->searchable(),
                TextColumn::make('subject_type')
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->badge(),
                TextColumn::make('nation'),
                TextColumn::make('source'),
                TextColumn::make('type'),
            ])
            ->filters([
                SelectFilter::make('subject_type')->options([
                    CaseSubjectType::SUBJECT_TYPE_INDIVIDUAL->value
                    => 'Individual',
                    CaseSubjectType::SUBJECT_TYPE_COMPANY->value =>
                    'Company',
                    CaseSubjectType::SUBJECT_TYPE_ORGANIZATION->value =>
                    'Organization',
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

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()->compact()->schema([
                    Fieldset::make('')
                        ->schema([
                            Infolists\Components\TextEntry::make('subject_type')
                                ->formatStateUsing(fn ($state) => $state->label())
                                ->badge()
                                ->color('success'),
                            Infolists\Components\TextEntry::make('case_type')->formatStateUsing(fn ($state) => $state->label())
                                ->badge()
                                ->color('info'),

                        ])
                        ->columns(2),

                ]),

                Section::make('Details')
                    ->description('Case Details')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('subject'),
                        Infolists\Components\TextEntry::make('person_in_charge'),
                        Infolists\Components\TextEntry::make('nation'),
                        Infolists\Components\TextEntry::make('case_date')->date(),
                        Infolists\Components\TextEntry::make('decision_number'),
                        Infolists\Components\TextEntry::make('beneficiary_ownership')->default('-'),

                        Split::make([
                            Infolists\Components\TextEntry::make('punishment_start')->date(),
                            Infolists\Components\TextEntry::make('punishment_end')->date(),
                        ])->columnSpanFull(),

                        Infolists\Components\TextEntry::make('source'),
                        Infolists\Components\TextEntry::make('year'),


                        Infolists\Components\TextEntry::make('link')->columnSpanFull(),


                    ]),

                Section::make('Summary')
                    ->description('Case Summary')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('summary_formatted')->markdown()
                            ->label('')
                            ->alignJustify()
                            ->columnSpanFull(),
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
