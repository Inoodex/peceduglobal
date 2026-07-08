<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Appointment;
use App\Models\BlockElement;
use App\Models\ConsultantSchedule;
use App\Models\Country;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogObserver
{
    private function log(Model $model, string $event): void
    {
        $user = auth()->user() ?? auth('api')->user() ?? request()->user() ?? request()->user('api');
        $description = $this->getDescription($model, $event);
        
        $oldValues = null;
        $newValues = null;

        if ($event === 'updated') {
            $newValues = collect($model->getChanges())->except(['updated_at', 'remember_token', 'password'])->toArray();
            $oldValues = collect($model->getOriginal())->only(array_keys($newValues))->toArray();
        } elseif ($event === 'created') {
            $newValues = $model->toArray();
        } elseif ($event === 'deleted') {
            $oldValues = $model->toArray();
        }

        ActivityLog::create([
            'user_id' => $user?->id,
            'event' => $event,
            'loggable_type' => get_class($model),
            'loggable_id' => $model->id,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    public function created(Model $model): void
    {
        $this->log($model, 'created');
    }

    public function updated(Model $model): void
    {
        if ($this->shouldSkipUpdateLog($model)) {
            return;
        }

        $this->log($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->log($model, 'deleted');
    }

    private function shouldSkipUpdateLog(Model $model): bool
    {
        if ($model instanceof User) {
            $changed = collect($model->getChanges())->except(['updated_at', 'remember_token', 'password', 'last_seen_at', 'last_login_at']);

            return $changed->isEmpty();
        }

        return false;
    }

    private function getDescription(Model $model, string $event): string
    {
        return match (get_class($model)) {
            Application::class => $this->applicationDescription($model, $event),
            Appointment::class => $this->appointmentDescription($model, $event),
            ConsultantSchedule::class => $this->slotDescription($model, $event),
            Inquiry::class => $this->inquiryDescription($model, $event),
            User::class => $this->userDescription($model, $event),
            Country::class => $this->countryDescription($model, $event),
            BlockElement::class => $this->elementDescription($model, $event),
            default => $this->genericDescription($model, $event),
        };
    }

    private function genericDescription(Model $model, string $event): string
    {
        $name = $this->getModelLabel($model);

        $description = ucfirst($event) . ' ' . class_basename($model) . ($name ? " '{$name}'" : '');
        if ($event === 'updated') {
            $description .= $this->updateDetails($model);
        }

        return $description;
    }

    private function getModelLabel(Model $model): string
    {
        if (method_exists($model, 'logTitle')) {
            return (string) $model->logTitle();
        }

        foreach (['application_number', 'title', 'name', 'full_name', 'course_name', 'slug', 'email', 'status'] as $label) {
            if (! empty($model->{$label})) {
                return (string) $model->{$label};
            }
        }

        return (string) $model->getKey();
    }

    private function applicationDescription(Application $application, string $event): string
    {
        $user = $application->consultant?->full_name ?? 'Consultant';
        $appNumber = $application->application_number ?? 'N/A';
        $status = $application->status ?? 'N/A';

        return match ($event) {
            'created' => "Created application {$appNumber} for {$user}",
            'updated' => "Updated application {$appNumber} (status: {$status})" . $this->updateDetails($application),
            'deleted' => "Deleted application {$appNumber}",
        };
    }

    private function appointmentDescription(Appointment $appointment, string $event): string
    {
        $consultant = $appointment->schedule?->consultant;
        $consultantName = $consultant?->full_name ?? 'N/A';
        $date = $appointment->schedule?->slot_date ?? 'N/A';
        $time = $appointment->schedule?->start_time ?? 'N/A';

        return match ($event) {
            'created' => $appointment->student_id
                ? "Booked appointment with {$consultantName} on {$date} at {$time}"
                : "Guest booked appointment with {$consultantName} on {$date} at {$time}",
            'updated' => "Updated appointment status to " . ucfirst($appointment->status ?? 'N/A') . $this->updateDetails($appointment),
            'deleted' => "Cancelled appointment with {$consultantName} on {$date}",
        };
    }

    private function inquiryDescription(Inquiry $inquiry, string $event): string
    {
        $name = trim(($inquiry->first_name ?? '') . ' ' . ($inquiry->last_name ?? ''));
        return match ($event) {
            'created' => "New inquiry ({$inquiry->type}) from {$name} ({$inquiry->email})",
            'updated' => "Updated inquiry from {$name}" . $this->updateDetails($inquiry),
            'deleted' => "Deleted inquiry from {$name}",
        };
    }

    private function userDescription(User $user, string $event): string
    {
        return match ($event) {
            'created' => "Created user '{$user->full_name}' with role {$user->role}",
            'updated' => "Updated user '{$user->full_name}'" . $this->updateDetails($user),
            'deleted' => "Deleted user '{$user->full_name}'",
        };
    }

    private function updateDetails(Model $model): string
    {
        $changes = collect($model->getChanges())->except(['updated_at', 'remember_token', 'password']);

        if ($changes->isEmpty()) {
            return '';
        }

        $fields = $changes->keys()->map(function ($field) {
            return ucwords(str_replace('_', ' ', $field));
        });

        return ' (' . $fields->join(', ') . ')';
    }

    private function countryDescription(Country $country, string $event): string
    {
        return match ($event) {
            'created' => "Created country '{$country->name}'",
            'updated' => "Updated country '{$country->name}'" . $this->updateDetails($country),
            'deleted' => "Deleted country '{$country->name}'",
        };
    }

    private function elementDescription(BlockElement $element, string $event): string
    {
        return match ($event) {
            'created' => "Created element '{$element->element_title}'",
            'updated' => "Updated element '{$element->element_title}'",
            'deleted' => "Deleted element '{$element->element_title}'",
        };
    }
}
