<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserAdminResource\Pages;
use App\Models\UserAdmin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserAdminResource extends Resource
{
    protected static ?string $model = UserAdmin::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Manajemen User';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama Lengkap')
                ->required(),

            Forms\Components\TextInput::make('email')
                ->email()
                ->unique(ignoreRecord: true)
                ->required(),

            Forms\Components\Select::make('role')
                ->options([
                    'administrator' => 'Administrator',
                    'operator_user' => 'Operator User',
                    'operator_transaksi' => 'Operator Transaksi',
                    'pengaduan' => 'Pengaduan',
                ])
                ->label('Role')
                ->required(),

            Forms\Components\TextInput::make('password')
                ->password()
                ->label('Password')
                ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                ->required(fn(string $context): bool => $context === 'create')
                ->maxLength(255)
                ->revealable()
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'administrator' => 'success',
                        'operator_user' => 'warning',
                        'operator_transaksi' => 'info',
                        'pengaduan' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->label('Dibuat Pada'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn() => self::currentUserIsAdmin()),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => self::currentUserIsAdmin()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => self::currentUserIsAdmin()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserAdmins::route('/'),
            'create' => Pages\CreateUserAdmin::route('/create'),
            'edit' => Pages\EditUserAdmin::route('/{record}/edit'),
        ];
    }

    protected static function currentUserIsAdmin(): bool
    {
        $user = Auth::user();

        if ($user instanceof UserAdmin) {
            return $user->isAdministrator();
        }

        return false;
    }

    public static function canCreate(): bool
    {
        return self::currentUserIsAdmin();
    }

    public static function canEdit($record): bool
    {
        return self::currentUserIsAdmin();
    }

    public static function canDelete($record): bool
    {
        return self::currentUserIsAdmin();
    }

    public static function canViewAny(): bool
    {
        return true;
    }
}
