<?php

namespace App\Models;

use App\Enums\OpportunityStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property OpportunityStatus $status
 * @property Carbon|null $open_date
 * @property Carbon|null $deadline
 * @property list<string>|null $eligible_countries
 * @property list<string>|null $eligible_organisation_types
 * @property list<string>|null $thematic_sectors
 */
#[Fillable([
    'slug', 'title', 'funder', 'funding_programme', 'summary', 'description',
    'minimum_award', 'maximum_award', 'currency', 'open_date', 'deadline',
    'eligible_countries', 'eligible_organisation_types', 'thematic_sectors',
    'eligibility_requirements', 'funding_type', 'opportunity_url', 'status',
    'featured', 'source_verified_at', 'image_url', 'image_alt', 'funder_url', 'learn_more_url',
])]
class GrantOpportunity extends Model
{
    use SoftDeletes;

    public function imageUrl(): ?string
    {
        return $this->image_path
            ? Storage::disk($this->image_disk ?? 'public')->url($this->image_path)
            : $this->image_url;
    }

    /** @return HasMany<OpportunityEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(OpportunityEvent::class);
    }

    /** @param Builder<GrantOpportunity> $query */
    public function scopePublished(Builder $query): void
    {
        $query->whereIn('status', [OpportunityStatus::Active, OpportunityStatus::ClosingSoon, OpportunityStatus::Closed]);
    }

    /** @param Builder<GrantOpportunity> $query */
    public function scopeOpen(Builder $query): void
    {
        $query->whereIn('status', [OpportunityStatus::Active, OpportunityStatus::ClosingSoon])
            ->where(fn (Builder $query) => $query->whereNull('deadline')->orWhereDate('deadline', '>=', today()))
            ->where(fn (Builder $query) => $query->whereNull('open_date')->orWhereDate('open_date', '<=', today()));
    }

    /** @param Builder<GrantOpportunity> $query */
    public function scopeClosed(Builder $query): void
    {
        $query->published()->where(fn (Builder $query) => $query->where('status', OpportunityStatus::Closed)->orWhereDate('deadline', '<', today()));
    }

    public function isPublic(): bool
    {
        return ! $this->trashed() && in_array($this->status, [OpportunityStatus::Active, OpportunityStatus::ClosingSoon, OpportunityStatus::Closed], true);
    }

    public function isOpen(): bool
    {
        return $this->isPublic() && $this->status !== OpportunityStatus::Closed
            && (! $this->deadline || $this->deadline->endOfDay()->isFuture())
            && (! $this->open_date || $this->open_date->startOfDay()->isPast());
    }

    public function publicStatusLabel(): string
    {
        if (! $this->isPublic()) {
            return $this->status->label();
        }
        if ($this->open_date?->startOfDay()->isFuture()) {
            return 'Opening soon';
        }

        return $this->isOpen() ? $this->status->label() : 'Closed';
    }

    /** @param Builder<GrantOpportunity> $query */
    public function scopeSearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search): void {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('funder', 'like', "%{$search}%")
                ->orWhere('summary', 'like', "%{$search}%")
                ->orWhere('thematic_sectors', 'like', "%{$search}%")
                ->orWhere('eligible_countries', 'like', "%{$search}%");
        });
    }

    public function awardRange(): string
    {
        $format = fn (?int $amount): string => $amount === null ? '—' : number_format($amount);

        if ($this->minimum_award === null && $this->maximum_award === null) {
            return 'Amount not specified';
        }

        if ($this->minimum_award === $this->maximum_award || $this->minimum_award === null) {
            return $this->currency.' '.$format($this->maximum_award);
        }

        return $this->currency.' '.$format($this->minimum_award).'–'.$format($this->maximum_award);
    }

    public function displayAmount(): string
    {
        $amount = $this->maximum_award ?? $this->minimum_award;

        if ($amount === null) {
            return 'Amount not specified';
        }

        $symbol = match ($this->currency) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => $this->currency.' ',
        };

        return $symbol.number_format($amount);
    }

    protected function casts(): array
    {
        return [
            'open_date' => 'date',
            'deadline' => 'date',
            'eligible_countries' => 'array',
            'eligible_organisation_types' => 'array',
            'thematic_sectors' => 'array',
            'status' => OpportunityStatus::class,
            'featured' => 'boolean',
            'source_verified_at' => 'datetime',
        ];
    }
}
