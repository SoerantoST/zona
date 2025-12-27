<?php

namespace App\Filament\Operatoruser\Resources;

use App\Filament\Operatoruser\Resources\NotificationResource\Pages;
use App\Models\Notification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Kirim Notifikasi Baru')
                    ->description('Isi formulir di bawah. Kosongkan "Pilih User" untuk mengirim ke SELURUH pengguna.')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            // ✅ DIUBAH: Dihapus required() dan ditambah nullable()
                            ->nullable() 
                            ->label('Pilih User (Broadcast jika kosong)')
                            ->helperText('Jika dikosongkan, semua user akan menerima notifikasi ini.'),

                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->label('Judul Notifikasi')
                            ->placeholder('Contoh: Update Aplikasi Tersedia!'),

                        Select::make('type')
                            ->options([
                                'info' => 'Informasi Umum',
                                'warning' => 'Peringatan',
                                'transaction' => 'Transaksi',
                                'system' => 'Sistem / Update',
                            ])
                            ->required()
                            ->label('Tipe Notifikasi'),

                        Textarea::make('message')
                            ->required()
                            ->rows(4)
                            ->label('Pesan Notifikasi')
                            ->placeholder('Tulis pesan lengkap di sini...'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Target User')
                    // ✅ DIUBAH: Menampilkan teks "Global/Semua" jika user_id null
                    ->default('Global (Semua User)') 
                    ->color(fn ($state) => $state === 'Global (Semua User)' ? 'primary' : null)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Judul')
                    ->limit(30)
                    ->searchable(),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'info' => 'info',
                        'warning' => 'warning',
                        'transaction' => 'success',
                        'system' => 'danger',
                        default => 'gray',
                    })
                    ->label('Tipe'),

                TextColumn::make('read_status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'read' ? 'success' : 'gray')
                    ->label('Status'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Waktu Kirim')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'info' => 'Info',
                        'warning' => 'Warning',
                        'transaction' => 'Transaksi',
                        'system' => 'Sistem',
                    ]),
                // Filter untuk melihat notifikasi Global saja
                Tables\Filters\Filter::make('is_global')
                    ->label('Hanya Global')
                    ->query(fn (Builder $query) => $query->whereNull('user_id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
        ];
    }
}