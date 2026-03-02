<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnrollmentResource\Pages;
use App\Models\Enrollment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'User Management';

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('course_id')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DateTimePicker::make('enrolled_at')
                    ->required()
                    ->default(now()),
                Forms\Components\DateTimePicker::make('completed_at')
                    ->nullable()
                    ->helperText('When the user completed the course'),
                Forms\Components\Section::make('Progress Information')
                    ->description('View and manage enrollment progress')
                    ->schema([
                        Forms\Components\Placeholder::make('progress_percentage')
                            ->label('Progress Percentage')
                            ->content(fn ($record): string => $record ? $record->progress_percentage . '%' : 'N/A'),
                        Forms\Components\Placeholder::make('total_lessons')
                            ->label('Total Lessons')
                            ->content(fn ($record): string => $record ? $record->course->lessons()->count() : 'N/A'),
                        Forms\Components\Placeholder::make('completed_lessons')
                            ->label('Completed Lessons')
                            ->content(fn ($record): string => $record ? $record->user->lessonProgresses()
                                ->whereHas('lesson', function ($query) use ($record) {
                                    $query->where('course_id', $record->course_id);
                                })
                                ->whereNotNull('completed_at')
                                ->count() : 'N/A'),
                    ])
                    ->columns(3)
                    ->visible(fn ($context) => $context === 'edit' || $context === 'view'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('user.email')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('course.title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->color(fn ($state) => match (true) {
                        $state >= 100 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Completed')
                    ->getStateUsing(fn (Enrollment $record): bool => $record->isCompleted())
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('enrolled_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not completed'),
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
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_completed')
                    ->label('Completed Status')
                    ->placeholder('All enrollments')
                    ->trueLabel('Completed')
                    ->falseLabel('In Progress')
                    ->query(fn ($query) => $query->whereNotNull('completed_at')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('mark_completed')
                    ->label('Mark as Completed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Enrollment $record) {
                        $record->update(['completed_at' => now()]);
                    })
                    ->visible(fn (Enrollment $record): bool => !$record->isCompleted()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_completed')
                        ->label('Mark as Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function (Enrollment $record) {
                                if (!$record->isCompleted()) {
                                    $record->update(['completed_at' => now()]);
                                }
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'view' => Pages\ViewEnrollment::route('/{record}'),
            'edit' => Pages\EditEnrollment::route('/{record}/edit'),
        ];
    }
}
