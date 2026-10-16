<?php

namespace App\Filament\Resources\Prescriptions;

use App\Filament\Resources\Prescriptions\Pages\CreatePrescription;
use App\Filament\Resources\Prescriptions\Pages\EditPrescription;
use App\Filament\Resources\Prescriptions\Pages\ListPrescriptions;
use App\Models\Prescription;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PrescriptionResource extends Resource
{
    protected static ?string $model = Prescription::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 7;

    public static function getNavigationLabel(): string
    {
        return __('navigation.prescriptions');
    }

    public static function getModelLabel(): string
    {
        return __('prescription.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('prescription.title_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label(__('prescription.customer'))
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('doctor_name')
                    ->label(__('prescription.doctor_name'))
                    ->maxLength(255),

                Textarea::make('notes')
                    ->label(__('prescription.notes'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label(__('prescription.customer'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('doctor_name')
                    ->label(__('prescription.doctor_name'))
                    ->searchable(),

                TextColumn::make('notes')
                    ->label(__('prescription.notes'))
                    ->limit(50)
                    ->toggleable(),

               TextColumn::make('created_at')
    ->label(__('prescription.created_at'))
    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '-')
    ->sortable()
    ->toggleable(isToggledHiddenByDefault: true),

TextColumn::make('updated_at')
    ->label(__('prescription.updated_at'))
    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '-')
    ->sortable()
    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrescriptions::route('/'),
            'create' => CreatePrescription::route('/create'),
            'edit' => EditPrescription::route('/{record}/edit'),
        ];
    }
}