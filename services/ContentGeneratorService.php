<?php

declare(strict_types=1);

class ContentGeneratorService
{
    public function generateLocationContent(array $data): string
    {
        if (empty($data['summary'])) {
            return '';
        }

        $s = $data['summary'];
        $pincode = $s['pincode'];
        $district = $s['district'];
        $state = $s['statename'];
        $officeCount = count($data['offices'] ?? []);

        $blocks = [
            "The PIN code {$pincode} belongs to {$district} district in {$state}. This delivery cluster includes {$officeCount} mapped post office records and supports both day-to-day mail handling and location-specific logistics coverage.",
            "Residents and businesses use pincode {$pincode} for addressing letters, parcels, government notices, and e-commerce shipments. The local postal ecosystem is coordinated through district-level sorting operations and regional bag routing policies.",
            "Postal services in {$district} are designed to maintain reliability across urban and semi-urban belts. Depending on the office type, facilities can include speed post booking, savings products, and delivery status coverage.",
            "At the state administration level, {$state} postal circles align district operations with national India Post service standards. That ensures standardized coding, transit planning, and dispatch flow from origin to destination.",
            "Delivery coverage for {$pincode} usually spans marketplaces, neighborhoods, villages, and institutional addresses attached to the listed branch offices. Addressing correctly with locality and office name improves successful last-mile delivery.",
            "Nearby locations linked with {$district} can share operational hubs, making this PIN code useful for route planning and service-area comparison. Users can explore related PIN codes below for nearby alternatives and neighboring service points.",
            "For each post office under {$pincode}, operational details such as division, region, and delivery type help visitors understand postal jurisdiction boundaries and expected service behavior.",
        ];

        return '<p>' . implode('</p><p>', $blocks) . '</p>';
    }
}
