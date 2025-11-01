<?php

namespace App\Filament\Resources\UserAdmins\Pages;

use App\Filament\Resources\UserAdmins\UserAdminResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserAdmin extends CreateRecord
{
    protected static string $resource = UserAdminResource::class;
}
