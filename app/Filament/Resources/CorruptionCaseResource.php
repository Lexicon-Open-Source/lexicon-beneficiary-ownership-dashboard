<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CorruptionCaseResource\Pages;
use App\Models\CorruptionCase;
use App\Models\Enums\CaseStatus;
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
                Select::make('subject_type')->options(CorruptionCase::SUBJECT_TYPE)->required()->native(false),
                TextInput::make('person_in_charge')->maxLength(255),
                TextInput::make('benificiary_ownership')->maxLength(255),
                DatePicker::make('case_date')->native(false),
                TextInput::make('decision_number')->required()->maxLength(255),
                TextInput::make('source')->required()->maxLength(255),
                TextInput::make('link')->required(),
                Select::make('nation')->options([
                    'Global' => 'Global',
                    'Indonesia' => 'Indonesia',
                    'Malaysia' => 'Malaysia',
                    'Singapore' => 'Singapore',
                ])->required()->native(false),
                DatePicker::make('punishment_start')->native(false),
                DatePicker::make('punishment_end')->native(false),
                Select::make('case_type')->options(CorruptionCase::CASE_TYPE)->required()->native(false),
                TextInput::make('year')->maxLength(4)->required(),
                Select::make('status')->options(CorruptionCase::CASE_STATUS)->required()->native(false),

                MarkdownEditor::make('summary_formatted')
                    ->toolbarButtons([
                        'blockquote',
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'heading',
                        'italic',
                        'link',
                        'orderedList',
                        'undo',
                        'redo',
                        'strike',
                        'table',

                    ])->label('Summary')->required()->columnSpanFull(),

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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

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
                            Infolists\Components\TextEntry::make('status')->formatStateUsing(fn ($state) => $state->label())
                                ->badge()
                                ->color('danger'),
                        ])
                        ->columns(3),

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
            'index' => Pages\ListCorruptionCases::route('/'),
            'create' => Pages\CreateCorruptionCase::route('/create'),
            'edit' => Pages\EditCorruptionCase::route('/{record}/edit'),
            'view' => Pages\ViewCorruptionCase::route('/{record}')
        ];
    }
}
