<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrateStateUsing(fn ($state) => $state ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                Forms\Components\Section::make('Enrollments')
                    ->description('Manage user course enrollments')
                    ->schema([
                        Forms\Components\Repeater::make('enrollments')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('course_id')
                                    ->relationship('course', 'title')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\DateTimePicker::make('enrolled_at')
                                    ->required()
                                    ->default(now()),
                                Forms\Components\DateTimePicker::make('completed_at')
                                    ->nullable(),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['course_id'] ? 'Course ID: ' . $state['course_id'] : 'New Enrollment'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(','),
                Tables\Columns\TextColumn::make('enrollments_count')
                    ->label('Enrollments')
                    ->getStateUsing(fn (User $record): int => $record->enrollments()->count())
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_courses_count')
                    ->label('Completed Courses')
                    ->getStateUsing(fn (User $record): int => $record->enrollments()->whereNotNull('completed_at')->count())
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('has_enrollments')
                    ->label('Has Enrollments')
                    ->placeholder('All users')
                    ->trueLabel('Has enrollments')
                    ->falseLabel('No enrollments')
                    ->query(fn ($query) => $query->whereHas('enrollments')),
                Tables\Filters\TernaryFilter::make('has_completed_courses')
                    ->label('Has Completed Courses')
                    ->placeholder('All users')
                    ->trueLabel('Has completed courses')
                    ->falseLabel('No completed courses')
                    ->query(fn ($query) => $query->whereHas('enrollments', fn ($q) => $q->whereNotNull('completed_at'))),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
