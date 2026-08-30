<?php

namespace Database\Seeders;

use App\Enums\OpportunityStatus;
use App\Models\GrantOpportunity;
use Illuminate\Database\Seeder;

class GrantOpportunitySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->opportunities() as $opportunity) {
            $grant = GrantOpportunity::withTrashed()->firstOrCreate(['slug' => $opportunity['slug']], $opportunity);
            if ($grant->title === $opportunity['title']) {
                foreach (['funder_url', 'learn_more_url', 'opportunity_url'] as $link) {
                    if (! $grant->{$link} && $opportunity[$link]) {
                        $grant->{$link} = $opportunity[$link];
                    }
                }
                $grant->save();
            }
        }

        GrantOpportunity::query()
            ->whereIn('slug', [
                'women-enterprise-growth-fund',
                'community-climate-resilience-challenge',
                'youth-skills-employment-initiative',
                'primary-health-access-innovation-fund',
                'girls-learning-leadership-fund',
                'civic-technology-accountability-grants',
            ])
            ->update(['status' => OpportunityStatus::Archived]);
    }

    /** @return list<array<string, mixed>> */
    private function opportunities(): array
    {
        return [
            $this->opportunity('resilient-communities', 'Resilient Communities Fund', 'Wellspring Foundation', 'Climate & Livelihoods', 'Grant', ['Kenya', 'Uganda', 'Tanzania'], 250000, '2026-09-14', 'Multi-year funding for community-led climate adaptation and resilient livelihoods in East Africa.', 'https://images.unsplash.com/photo-1553775927-a071d5a6a39a?w=900&h=600&fit=crop&auto=format', 'Community members carrying water across a rural landscape', 'https://wellspringfoundation.org/resilient-communities/apply', 'https://wellspringfoundation.org'),
            $this->opportunity('girls-education', "Girls' Education Initiative", 'Meridian Trust', 'Education', 'Grant', ['Nigeria', 'Ghana'], 120000, '2026-10-02', 'Support for organisations improving access, learning outcomes, and opportunity for girls and young women.', 'https://images.unsplash.com/photo-1604525241109-c3b7eecf4add?w=900&h=600&fit=crop&auto=format', 'Young woman studying on a laptop in a classroom', 'https://meridiantrust.org/girls-education/apply', 'https://meridiantrust.org'),
            $this->opportunity('community-health', 'Community Health Access', 'Open Horizons Fund', 'Public Health', 'Partnership', ['India', 'Bangladesh'], 300000, '2026-10-20', 'Partnership funding for community-led programmes expanding equitable access to essential health services.', 'https://images.unsplash.com/photo-1774979161296-bb930552543a?w=900&h=600&fit=crop&auto=format', 'Two women seated outside a community health clinic', 'https://openhorizonsfund.org/community-health/partner', 'https://openhorizonsfund.org'),
            $this->opportunity('smallholder-agriculture', 'Smallholder Agriculture Grant', 'Harvest Futures', 'Food Security', 'Grant', ['Rwanda', 'Zambia'], 210000, '2026-11-11', 'Funding for locally led initiatives that strengthen smallholder livelihoods and resilient food systems.', 'https://images.unsplash.com/photo-1605000797499-95a51c5269ae?w=900&h=600&fit=crop&auto=format', 'Farmer carrying produce in an agricultural setting', 'https://harvestfutures.org/smallholder-grant/apply', 'https://harvestfutures.org'),
            $this->opportunity('community-solar', 'Community Solar Access', 'Lumen Energy Fund', 'Clean Energy', 'Partnership', ['Senegal', 'Mali'], 340000, '2026-12-03', 'Partnership support for organisations extending affordable, community-owned solar access.', 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?w=900&h=600&fit=crop&auto=format', 'Solar panels beneath a clear blue sky', 'https://lumenenergy.org/community-solar/partner', 'https://lumenenergy.org'),
            array_replace($this->opportunity('water-sanitation', 'Water & Sanitation Fund', 'Blue Rivers Alliance', 'WASH', 'Grant', ['Ethiopia', 'Malawi'], 180000, '2026-02-28', 'Funds clean water infrastructure and community sanitation programmes in rural districts.', 'https://images.unsplash.com/photo-1751609492149-e93de149a832?w=900&h=600&fit=crop&auto=format', 'People drawing water from a well in a rural village', 'https://bluerivers.org/wash-fund/apply', 'https://bluerivers.org'), ['status' => OpportunityStatus::Closed, 'open_date' => '2026-01-01']),
        ];
    }

    /**
     * @param  list<string>  $countries
     * @return array<string, mixed>
     */
    private function opportunity(
        string $slug,
        string $title,
        string $funder,
        string $sector,
        string $type,
        array $countries,
        int $amount,
        string $deadline,
        string $summary,
        string $imageUrl,
        string $imageAlt,
        ?string $opportunityUrl = null,
        ?string $funderUrl = null,
    ): array {
        return [
            'slug' => $slug,
            'title' => $title,
            'funder' => $funder,
            'summary' => $summary,
            'description' => 'This programme supports organisations delivering measurable outcomes in '.strtolower($sector).'. Successful applicants typically demonstrate strong community engagement, transparent financial management, and evidence of prior impact.',
            'minimum_award' => $amount,
            'maximum_award' => $amount,
            'currency' => 'USD',
            'open_date' => '2026-08-01',
            'deadline' => $deadline,
            'eligible_countries' => $countries,
            'eligible_organisation_types' => ['Nonprofit', 'NGO'],
            'thematic_sectors' => [$sector],
            'eligibility_requirements' => 'Applicants should be legally registered, demonstrate relevant programme experience, and provide evidence of prior impact.',
            'funding_type' => $type,
            'opportunity_url' => $opportunityUrl,
            'funder_url' => $funderUrl,
            'learn_more_url' => $opportunityUrl ? preg_replace('~/(apply|partner)$~', '', $opportunityUrl) : null,
            'image_url' => $imageUrl,
            'image_alt' => $imageAlt,
            'status' => OpportunityStatus::Active,
            'featured' => in_array($slug, ['resilient-communities', 'girls-education', 'community-health'], true),
            'source_verified_at' => now(),
        ];
    }
}
