<?php

namespace App\Filament\Resources\UserAdmins;

use App\Filament\Resources\UserAdmins\Pages\CreateUserAdmin;
use App\Filament\Resources\UserAdmins\Pages\EditUserAdmin;
use App\Filament\Resources\UserAdmins\Pages\ListUserAdmins;
use App\Filament\Resources\UserAdmins\Schemas\UserAdminForm;
use App\Filament\Resources\UserAdmins\Tables\UserAdminsTable;
use App\Models\UserAdmin;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserAdminResource extends Resource
{
    protected static ?string $model = UserAdmin::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'AdminUser';

    public static function form(Schema $schema): Schema
    {
        return UserAdminForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserAdminsTable::configure($table);
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
            'index' => ListUserAdmins::route('/'),
            'create' => CreateUserAdmin::route('/create'),
            'edit' => EditUserAdmin::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
