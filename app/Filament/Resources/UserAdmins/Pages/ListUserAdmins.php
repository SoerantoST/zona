<?php

namespace App\Filament\Resources\UserAdmins\Pages;

use App\Filament\Resources\UserAdmins\UserAdminResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserAdmins extends ListRecords
{
    protected static string $resource = UserAdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
