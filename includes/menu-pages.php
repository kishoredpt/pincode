<?php

declare(strict_types=1);

function getMenuPages(): array
{
    return [
        [
            'slug' => 'national-logistics-corridors',
            'title' => 'National Logistics Corridors and PIN Distribution',
            'category' => 'India Maps',
            'subsection' => 'Logistics Planning',
            'region' => 'India',
            'anchor_city' => 'Nagpur',
            'map' => ['lat' => 21.1458, 'lon' => 79.0882, 'zoom' => 5],
            'stats' => ['population_mn' => 1428, 'urbanization_pct' => 36.8, 'post_offices' => 155000, 'air_cargo_mt' => 3.4],
        ],
        [
            'slug' => 'coastal-shipping-pin-hubs',
            'title' => 'Coastal Shipping PIN Hubs and Port Hinterlands',
            'category' => 'India Maps',
            'subsection' => 'Maritime Networks',
            'region' => 'West & East Coasts',
            'anchor_city' => 'Chennai',
            'map' => ['lat' => 13.0827, 'lon' => 80.2707, 'zoom' => 5],
            'stats' => ['population_mn' => 420, 'urbanization_pct' => 48.1, 'post_offices' => 32100, 'air_cargo_mt' => 1.8],
        ],
        [
            'slug' => 'industrial-cluster-address-zones',
            'title' => 'Industrial Cluster Address Zones and PIN Precision',
            'category' => 'Business',
            'subsection' => 'Manufacturing Clusters',
            'region' => 'Western India',
            'anchor_city' => 'Ahmedabad',
            'map' => ['lat' => 23.0225, 'lon' => 72.5714, 'zoom' => 6],
            'stats' => ['population_mn' => 168, 'urbanization_pct' => 44.3, 'post_offices' => 18900, 'air_cargo_mt' => 0.9],
        ],
        [
            'slug' => 'tourism-circuits-postal-readiness',
            'title' => 'Tourism Circuits and Postal Readiness Framework',
            'category' => 'Travel',
            'subsection' => 'Destination Systems',
            'region' => 'Pan India',
            'anchor_city' => 'Jaipur',
            'map' => ['lat' => 26.9124, 'lon' => 75.7873, 'zoom' => 6],
            'stats' => ['population_mn' => 250, 'urbanization_pct' => 39.7, 'post_offices' => 29400, 'air_cargo_mt' => 0.7],
        ],
        [
            'slug' => 'education-cities-address-planning',
            'title' => 'Education Cities and Campus Address Planning',
            'category' => 'Education',
            'subsection' => 'Academic Geography',
            'region' => 'North & South India',
            'anchor_city' => 'Pune',
            'map' => ['lat' => 18.5204, 'lon' => 73.8567, 'zoom' => 6],
            'stats' => ['population_mn' => 130, 'urbanization_pct' => 57.2, 'post_offices' => 14200, 'air_cargo_mt' => 0.55],
        ],
        [
            'slug' => 'agri-market-yards-and-delivery-grids',
            'title' => 'Agri Market Yards and Rural Delivery Grids',
            'category' => 'My India',
            'subsection' => 'Rural Economy',
            'region' => 'Central India',
            'anchor_city' => 'Indore',
            'map' => ['lat' => 22.7196, 'lon' => 75.8577, 'zoom' => 6],
            'stats' => ['population_mn' => 310, 'urbanization_pct' => 31.4, 'post_offices' => 33800, 'air_cargo_mt' => 0.45],
        ],
        [
            'slug' => 'northeast-connectivity-map',
            'title' => 'North East Connectivity Map and PIN Access',
            'category' => 'India Maps',
            'subsection' => 'Regional Integration',
            'region' => 'North East India',
            'anchor_city' => 'Guwahati',
            'map' => ['lat' => 26.1445, 'lon' => 91.7362, 'zoom' => 6],
            'stats' => ['population_mn' => 54, 'urbanization_pct' => 25.6, 'post_offices' => 5100, 'air_cargo_mt' => 0.21],
        ],
        [
            'slug' => 'himalayan-states-logistics-map',
            'title' => 'Himalayan States Logistics Map and Weather Routing',
            'category' => 'Travel',
            'subsection' => 'Mountain Operations',
            'region' => 'Himalayan Belt',
            'anchor_city' => 'Dehradun',
            'map' => ['lat' => 30.3165, 'lon' => 78.0322, 'zoom' => 6],
            'stats' => ['population_mn' => 62, 'urbanization_pct' => 21.1, 'post_offices' => 7600, 'air_cargo_mt' => 0.12],
        ],
        [
            'slug' => 'metro-last-mile-performance-map',
            'title' => 'Metro Last-Mile Performance and PIN Behavior',
            'category' => 'Business',
            'subsection' => 'Urban Delivery',
            'region' => 'Tier-1 Metro Regions',
            'anchor_city' => 'Bengaluru',
            'map' => ['lat' => 12.9716, 'lon' => 77.5946, 'zoom' => 6],
            'stats' => ['population_mn' => 180, 'urbanization_pct' => 78.4, 'post_offices' => 12300, 'air_cargo_mt' => 2.1],
        ],
        [
            'slug' => 'healthcare-logistics-and-cold-chain',
            'title' => 'Healthcare Logistics and Cold-Chain Addressing',
            'category' => 'My India',
            'subsection' => 'Public Services',
            'region' => 'National Health Network',
            'anchor_city' => 'Hyderabad',
            'map' => ['lat' => 17.3850, 'lon' => 78.4867, 'zoom' => 6],
            'stats' => ['population_mn' => 220, 'urbanization_pct' => 52.7, 'post_offices' => 17600, 'air_cargo_mt' => 1.3],
        ],
        [
            'slug' => 'digital-commerce-fulfilment-zones',
            'title' => 'Digital Commerce Fulfilment Zones by PIN Cluster',
            'category' => 'Business',
            'subsection' => 'E-commerce Operations',
            'region' => 'National E-commerce Belt',
            'anchor_city' => 'Mumbai',
            'map' => ['lat' => 19.0760, 'lon' => 72.8777, 'zoom' => 6],
            'stats' => ['population_mn' => 340, 'urbanization_pct' => 67.5, 'post_offices' => 25500, 'air_cargo_mt' => 2.9],
        ],
        [
            'slug' => 'women-led-enterprise-address-map',
            'title' => 'Women-led Enterprise Address Map and Market Reach',
            'category' => 'My India',
            'subsection' => 'Inclusive Growth',
            'region' => 'Mixed Urban-Rural Districts',
            'anchor_city' => 'Lucknow',
            'map' => ['lat' => 26.8467, 'lon' => 80.9462, 'zoom' => 6],
            'stats' => ['population_mn' => 260, 'urbanization_pct' => 33.2, 'post_offices' => 28400, 'air_cargo_mt' => 0.52],
        ],
        [
            'slug' => 'rail-freight-terminals-and-pin-coverage',
            'title' => 'Rail Freight Terminals and PIN Coverage Hierarchy',
            'category' => 'India Maps',
            'subsection' => 'Freight Systems',
            'region' => 'Dedicated Freight Corridors',
            'anchor_city' => 'Kanpur',
            'map' => ['lat' => 26.4499, 'lon' => 80.3319, 'zoom' => 6],
            'stats' => ['population_mn' => 205, 'urbanization_pct' => 42.8, 'post_offices' => 19800, 'air_cargo_mt' => 0.4],
        ],
        [
            'slug' => 'water-stress-districts-and-service-planning',
            'title' => 'Water-Stress Districts and Postal Service Planning',
            'category' => 'My India',
            'subsection' => 'Climate Adaptation',
            'region' => 'Semi-arid Belt',
            'anchor_city' => 'Aurangabad',
            'map' => ['lat' => 19.8762, 'lon' => 75.3433, 'zoom' => 6],
            'stats' => ['population_mn' => 140, 'urbanization_pct' => 36.5, 'post_offices' => 16700, 'air_cargo_mt' => 0.31],
        ],
        [
            'slug' => 'language-zones-and-address-standardization',
            'title' => 'Language Zones and Address Standardization Protocol',
            'category' => 'Education',
            'subsection' => 'Language Geography',
            'region' => 'Multilingual India',
            'anchor_city' => 'Bhopal',
            'map' => ['lat' => 23.2599, 'lon' => 77.4126, 'zoom' => 5],
            'stats' => ['population_mn' => 480, 'urbanization_pct' => 37.9, 'post_offices' => 60200, 'air_cargo_mt' => 1.1],
        ],
        [
            'slug' => 'defence-cantonments-and-secure-delivery',
            'title' => 'Defence Cantonments and Secure Delivery Pathways',
            'category' => 'My India',
            'subsection' => 'Secure Networks',
            'region' => 'Strategic Installations',
            'anchor_city' => 'Secunderabad',
            'map' => ['lat' => 17.4399, 'lon' => 78.4983, 'zoom' => 6],
            'stats' => ['population_mn' => 72, 'urbanization_pct' => 61.2, 'post_offices' => 8200, 'air_cargo_mt' => 0.26],
        ],
        [
            'slug' => 'border-districts-and-essential-supply-post',
            'title' => 'Border Districts and Essential Supply Postal Routes',
            'category' => 'India Maps',
            'subsection' => 'Border Logistics',
            'region' => 'International Border Belt',
            'anchor_city' => 'Amritsar',
            'map' => ['lat' => 31.6340, 'lon' => 74.8723, 'zoom' => 6],
            'stats' => ['population_mn' => 88, 'urbanization_pct' => 29.9, 'post_offices' => 11200, 'air_cargo_mt' => 0.24],
        ],
        [
            'slug' => 'smart-cities-addressing-readiness',
            'title' => 'Smart Cities Addressing Readiness and Service Levels',
            'category' => 'Business',
            'subsection' => 'Urban Policy',
            'region' => 'Smart City Mission Regions',
            'anchor_city' => 'Surat',
            'map' => ['lat' => 21.1702, 'lon' => 72.8311, 'zoom' => 6],
            'stats' => ['population_mn' => 96, 'urbanization_pct' => 71.5, 'post_offices' => 6900, 'air_cargo_mt' => 0.48],
        ],
        [
            'slug' => 'heritage-cities-and-conservation-delivery',
            'title' => 'Heritage Cities and Conservation-Friendly Delivery',
            'category' => 'Travel',
            'subsection' => 'Heritage Circuits',
            'region' => 'Historic Urban Centres',
            'anchor_city' => 'Varanasi',
            'map' => ['lat' => 25.3176, 'lon' => 82.9739, 'zoom' => 6],
            'stats' => ['population_mn' => 84, 'urbanization_pct' => 49.3, 'post_offices' => 10100, 'air_cargo_mt' => 0.2],
        ],
        [
            'slug' => 'river-basin-economies-and-postal-service',
            'title' => 'River Basin Economies and Postal Service Design',
            'category' => 'India Maps',
            'subsection' => 'Hydrological Regions',
            'region' => 'Major River Basins',
            'anchor_city' => 'Patna',
            'map' => ['lat' => 25.5941, 'lon' => 85.1376, 'zoom' => 6],
            'stats' => ['population_mn' => 310, 'urbanization_pct' => 19.5, 'post_offices' => 34700, 'air_cargo_mt' => 0.36],
        ],
        [
            'slug' => 'renewable-energy-parks-and-supply-routes',
            'title' => 'Renewable Energy Parks and Supply Route Addressing',
            'category' => 'Business',
            'subsection' => 'Energy Geography',
            'region' => 'Solar & Wind Corridors',
            'anchor_city' => 'Jodhpur',
            'map' => ['lat' => 26.2389, 'lon' => 73.0243, 'zoom' => 6],
            'stats' => ['population_mn' => 74, 'urbanization_pct' => 28.4, 'post_offices' => 9500, 'air_cargo_mt' => 0.19],
        ],
        [
            'slug' => 'fisheries-hubs-and-coastal-cold-chains',
            'title' => 'Fisheries Hubs and Coastal Cold Chain PIN Maps',
            'category' => 'Travel',
            'subsection' => 'Blue Economy',
            'region' => 'Coastal District Network',
            'anchor_city' => 'Kochi',
            'map' => ['lat' => 9.9312, 'lon' => 76.2673, 'zoom' => 6],
            'stats' => ['population_mn' => 58, 'urbanization_pct' => 54.9, 'post_offices' => 7300, 'air_cargo_mt' => 0.41],
        ],
        [
            'slug' => 'textile-belts-and-export-address-quality',
            'title' => 'Textile Belts and Export Address Quality Controls',
            'category' => 'Business',
            'subsection' => 'Export Clusters',
            'region' => 'Textile Production Belt',
            'anchor_city' => 'Tiruppur',
            'map' => ['lat' => 11.1085, 'lon' => 77.3411, 'zoom' => 6],
            'stats' => ['population_mn' => 92, 'urbanization_pct' => 62.7, 'post_offices' => 8100, 'air_cargo_mt' => 0.62],
        ],
        [
            'slug' => 'forest-districts-and-last-mile-post',
            'title' => 'Forest Districts and Last-Mile Postal Access',
            'category' => 'My India',
            'subsection' => 'Ecological Regions',
            'region' => 'Forest-Rich States',
            'anchor_city' => 'Raipur',
            'map' => ['lat' => 21.2514, 'lon' => 81.6296, 'zoom' => 6],
            'stats' => ['population_mn' => 116, 'urbanization_pct' => 24.8, 'post_offices' => 14900, 'air_cargo_mt' => 0.22],
        ],
        [
            'slug' => 'aviation-hubs-and-express-parcel-zones',
            'title' => 'Aviation Hubs and Express Parcel Zone Mapping',
            'category' => 'Travel',
            'subsection' => 'Airport Economies',
            'region' => 'Major Aviation Corridors',
            'anchor_city' => 'Delhi',
            'map' => ['lat' => 28.6139, 'lon' => 77.2090, 'zoom' => 6],
            'stats' => ['population_mn' => 260, 'urbanization_pct' => 68.9, 'post_offices' => 14100, 'air_cargo_mt' => 3.1],
        ],
        [
            'slug' => 'judicial-circuits-and-legal-document-delivery',
            'title' => 'Judicial Circuits and Legal Document Delivery Paths',
            'category' => 'My India',
            'subsection' => 'Governance Networks',
            'region' => 'Court Jurisdiction Regions',
            'anchor_city' => 'Prayagraj',
            'map' => ['lat' => 25.4358, 'lon' => 81.8463, 'zoom' => 6],
            'stats' => ['population_mn' => 134, 'urbanization_pct' => 35.2, 'post_offices' => 17200, 'air_cargo_mt' => 0.29],
        ],
        [
            'slug' => 'startup-corridors-and-address-risk',
            'title' => 'Startup Corridors and Address Risk Management',
            'category' => 'Business',
            'subsection' => 'Innovation Hubs',
            'region' => 'Startup Ecosystem Nodes',
            'anchor_city' => 'Gurugram',
            'map' => ['lat' => 28.4595, 'lon' => 77.0266, 'zoom' => 7],
            'stats' => ['population_mn' => 65, 'urbanization_pct' => 82.1, 'post_offices' => 3600, 'air_cargo_mt' => 0.87],
        ],
        [
            'slug' => 'tribal-regions-service-coverage-map',
            'title' => 'Tribal Regions Service Coverage and Inclusion Map',
            'category' => 'My India',
            'subsection' => 'Social Inclusion',
            'region' => 'Scheduled Area Districts',
            'anchor_city' => 'Ranchi',
            'map' => ['lat' => 23.3441, 'lon' => 85.3096, 'zoom' => 6],
            'stats' => ['population_mn' => 102, 'urbanization_pct' => 22.6, 'post_offices' => 13800, 'air_cargo_mt' => 0.18],
        ],
        [
            'slug' => 'food-processing-belts-logistics-map',
            'title' => 'Food Processing Belts and Cold Logistics PIN Maps',
            'category' => 'Business',
            'subsection' => 'Food Supply Chains',
            'region' => 'Agro-Processing Clusters',
            'anchor_city' => 'Nashik',
            'map' => ['lat' => 19.9975, 'lon' => 73.7898, 'zoom' => 6],
            'stats' => ['population_mn' => 122, 'urbanization_pct' => 46.7, 'post_offices' => 15600, 'air_cargo_mt' => 0.53],
        ],
        [
            'slug' => 'student-migration-cities-map',
            'title' => 'Student Migration Cities and Postal Demand Mapping',
            'category' => 'Education',
            'subsection' => 'Student Mobility',
            'region' => 'Higher Education Nodes',
            'anchor_city' => 'Kota',
            'map' => ['lat' => 25.2138, 'lon' => 75.8648, 'zoom' => 6],
            'stats' => ['population_mn' => 44, 'urbanization_pct' => 58.6, 'post_offices' => 5200, 'air_cargo_mt' => 0.11],
        ],
        [
            'slug' => 'interstate-bus-terminals-delivery-access',
            'title' => 'Interstate Bus Terminals and Delivery Access Grid',
            'category' => 'Travel',
            'subsection' => 'Surface Mobility',
            'region' => 'Interstate Mobility Network',
            'anchor_city' => 'Bhubaneswar',
            'map' => ['lat' => 20.2961, 'lon' => 85.8245, 'zoom' => 6],
            'stats' => ['population_mn' => 88, 'urbanization_pct' => 31.8, 'post_offices' => 11400, 'air_cargo_mt' => 0.27],
        ],
        [
            'slug' => 'district-skilling-centres-and-job-mail',
            'title' => 'District Skilling Centres and Employment Mail Flows',
            'category' => 'Education',
            'subsection' => 'Workforce Development',
            'region' => 'Emerging Employment Districts',
            'anchor_city' => 'Nagpur',
            'map' => ['lat' => 21.1458, 'lon' => 79.0882, 'zoom' => 6],
            'stats' => ['population_mn' => 154, 'urbanization_pct' => 40.9, 'post_offices' => 18700, 'air_cargo_mt' => 0.34],
        ],
        [
            'slug' => 'disaster-response-logistics-map',
            'title' => 'Disaster Response Logistics and Emergency PIN Mapping',
            'category' => 'My India',
            'subsection' => 'Resilience Planning',
            'region' => 'Flood-Cyclone-Earthquake Zones',
            'anchor_city' => 'Bhubaneswar',
            'map' => ['lat' => 20.2961, 'lon' => 85.8245, 'zoom' => 5],
            'stats' => ['population_mn' => 290, 'urbanization_pct' => 29.2, 'post_offices' => 36100, 'air_cargo_mt' => 0.9],
        ],
        [
            'slug' => 'pincode-near-me-search-intelligence',
            'title' => 'PIN Code Near Me Intelligence and Locality Match System',
            'category' => 'PIN Finder',
            'subsection' => 'Near Me Search',
            'region' => 'Urban and Semi-Urban India',
            'anchor_city' => 'New Delhi',
            'map' => ['lat' => 28.6139, 'lon' => 77.2090, 'zoom' => 6],
            'stats' => ['population_mn' => 510, 'urbanization_pct' => 61.4, 'post_offices' => 28900, 'air_cargo_mt' => 1.45],
        ],
        [
            'slug' => 'village-pincode-directory-india',
            'title' => 'Village PIN Code Directory and Gram Address Discovery',
            'category' => 'Rural PIN Data',
            'subsection' => 'Village Directory',
            'region' => 'Rural India',
            'anchor_city' => 'Lucknow',
            'map' => ['lat' => 26.8467, 'lon' => 80.9462, 'zoom' => 6],
            'stats' => ['population_mn' => 780, 'urbanization_pct' => 28.1, 'post_offices' => 98200, 'air_cargo_mt' => 0.74],
        ],
        [
            'slug' => 'courier-serviceability-by-pincode',
            'title' => 'Courier Serviceability by PIN Code and Delivery Promise Zones',
            'category' => 'Courier Tools',
            'subsection' => 'Serviceability Checker',
            'region' => 'National Courier Lanes',
            'anchor_city' => 'Mumbai',
            'map' => ['lat' => 19.0760, 'lon' => 72.8777, 'zoom' => 6],
            'stats' => ['population_mn' => 420, 'urbanization_pct' => 64.3, 'post_offices' => 24400, 'air_cargo_mt' => 2.35],
        ],
        [
            'slug' => 'post-office-nearby-contact-directory',
            'title' => 'Post Office Nearby Contact Directory and PIN Helpdesk Map',
            'category' => 'Post Office Help',
            'subsection' => 'Nearby Offices',
            'region' => 'District Service Network',
            'anchor_city' => 'Kolkata',
            'map' => ['lat' => 22.5726, 'lon' => 88.3639, 'zoom' => 6],
            'stats' => ['population_mn' => 300, 'urbanization_pct' => 47.8, 'post_offices' => 38400, 'air_cargo_mt' => 0.88],
        ],
        [
            'slug' => 'address-format-with-pincode-guide',
            'title' => 'Indian Address Format with PIN Code for Forms and KYC',
            'category' => 'Address Guide',
            'subsection' => 'Address Format',
            'region' => 'All India Compliance Workflows',
            'anchor_city' => 'Pune',
            'map' => ['lat' => 18.5204, 'lon' => 73.8567, 'zoom' => 6],
            'stats' => ['population_mn' => 260, 'urbanization_pct' => 58.2, 'post_offices' => 21100, 'air_cargo_mt' => 0.67],
        ],
        [
            'slug' => 'pincode-search-by-area-name',
            'title' => 'PIN Code Search by Area Name, Locality, and Landmark',
            'category' => 'PIN Finder',
            'subsection' => 'Area Name Lookup',
            'region' => 'Metro and Tier-2 India',
            'anchor_city' => 'Noida',
            'map' => ['lat' => 28.5355, 'lon' => 77.3910, 'zoom' => 7],
            'stats' => ['population_mn' => 360, 'urbanization_pct' => 66.8, 'post_offices' => 22100, 'air_cargo_mt' => 1.08],
        ],
        [
            'slug' => 'my-location-pincode-finder',
            'title' => 'My Location PIN Code Finder for Fast Address Discovery',
            'category' => 'PIN Finder',
            'subsection' => 'GPS Based Finder',
            'region' => 'Smartphone User Corridors',
            'anchor_city' => 'Hyderabad',
            'map' => ['lat' => 17.3850, 'lon' => 78.4867, 'zoom' => 7],
            'stats' => ['population_mn' => 310, 'urbanization_pct' => 63.4, 'post_offices' => 19200, 'air_cargo_mt' => 1.16],
        ],
        [
            'slug' => 'gram-panchayat-pincode-list',
            'title' => 'Gram Panchayat PIN Code List for Rural Service Access',
            'category' => 'Rural PIN Data',
            'subsection' => 'Panchayat Data',
            'region' => 'Village Governance Network',
            'anchor_city' => 'Patna',
            'map' => ['lat' => 25.5941, 'lon' => 85.1376, 'zoom' => 6],
            'stats' => ['population_mn' => 640, 'urbanization_pct' => 24.7, 'post_offices' => 84300, 'air_cargo_mt' => 0.51],
        ],
        [
            'slug' => 'tehsil-wise-pincode-database',
            'title' => 'Tehsil-wise PIN Code Database for Rural and Semi-Urban India',
            'category' => 'Rural PIN Data',
            'subsection' => 'Tehsil Directory',
            'region' => 'District and Tehsil Systems',
            'anchor_city' => 'Jaipur',
            'map' => ['lat' => 26.9124, 'lon' => 75.7873, 'zoom' => 6],
            'stats' => ['population_mn' => 520, 'urbanization_pct' => 33.5, 'post_offices' => 67900, 'air_cargo_mt' => 0.58],
        ],
        [
            'slug' => 'same-day-courier-pincode-zones',
            'title' => 'Same Day Courier PIN Code Zones and Cutoff Intelligence',
            'category' => 'Courier Tools',
            'subsection' => 'Same Day Zones',
            'region' => 'Express Delivery Cities',
            'anchor_city' => 'Chennai',
            'map' => ['lat' => 13.0827, 'lon' => 80.2707, 'zoom' => 7],
            'stats' => ['population_mn' => 210, 'urbanization_pct' => 72.9, 'post_offices' => 13100, 'air_cargo_mt' => 1.89],
        ],
        [
            'slug' => 'cod-prepaid-serviceability-pincode',
            'title' => 'COD and Prepaid Serviceability by PIN Code for Ecommerce',
            'category' => 'Courier Tools',
            'subsection' => 'COD Coverage',
            'region' => 'Ecommerce Order Lanes',
            'anchor_city' => 'Gurugram',
            'map' => ['lat' => 28.4595, 'lon' => 77.0266, 'zoom' => 7],
            'stats' => ['population_mn' => 270, 'urbanization_pct' => 74.6, 'post_offices' => 14700, 'air_cargo_mt' => 2.02],
        ],
        [
            'slug' => 'head-post-office-contact-by-pincode',
            'title' => 'Head Post Office Contact Directory by PIN Code',
            'category' => 'Post Office Help',
            'subsection' => 'Head Office Contacts',
            'region' => 'Circle and Division Network',
            'anchor_city' => 'Nagpur',
            'map' => ['lat' => 21.1458, 'lon' => 79.0882, 'zoom' => 6],
            'stats' => ['population_mn' => 180, 'urbanization_pct' => 43.6, 'post_offices' => 27400, 'air_cargo_mt' => 0.62],
        ],
        [
            'slug' => 'speed-post-branch-locator-india',
            'title' => 'Speed Post Branch Locator and Counter Timings by PIN',
            'category' => 'Post Office Help',
            'subsection' => 'Speed Post Locator',
            'region' => 'National Speed Post Grid',
            'anchor_city' => 'Ahmedabad',
            'map' => ['lat' => 23.0225, 'lon' => 72.5714, 'zoom' => 6],
            'stats' => ['population_mn' => 240, 'urbanization_pct' => 57.8, 'post_offices' => 20600, 'air_cargo_mt' => 0.95],
        ],
        [
            'slug' => 'address-proof-format-with-pincode',
            'title' => 'Address Proof Format with PIN Code for Bank and KYC',
            'category' => 'Address Guide',
            'subsection' => 'KYC Templates',
            'region' => 'Financial and Identity Services',
            'anchor_city' => 'Mumbai',
            'map' => ['lat' => 19.0760, 'lon' => 72.8777, 'zoom' => 6],
            'stats' => ['population_mn' => 290, 'urbanization_pct' => 70.2, 'post_offices' => 18800, 'air_cargo_mt' => 1.27],
        ],
        [
            'slug' => 'resume-job-application-address-format',
            'title' => 'Resume and Job Application Address Format with Correct PIN',
            'category' => 'Address Guide',
            'subsection' => 'Job and Resume Format',
            'region' => 'Employment and Hiring Markets',
            'anchor_city' => 'Pune',
            'map' => ['lat' => 18.5204, 'lon' => 73.8567, 'zoom' => 7],
            'stats' => ['population_mn' => 205, 'urbanization_pct' => 62.9, 'post_offices' => 13900, 'air_cargo_mt' => 0.73],
        ],
        [
            'slug' => 'pincode-validation-api-integration-guide',
            'title' => 'PIN Code Validation API Integration Guide for Checkout',
            'category' => 'Developer API',
            'subsection' => 'API Integration',
            'region' => 'Online Retail Platforms',
            'anchor_city' => 'Bengaluru',
            'map' => ['lat' => 12.9716, 'lon' => 77.5946, 'zoom' => 7],
            'stats' => ['population_mn' => 190, 'urbanization_pct' => 71.4, 'post_offices' => 12100, 'air_cargo_mt' => 1.39],
        ],
        [
            'slug' => 'address-autocomplete-with-pincode-api',
            'title' => 'Address Autocomplete with PIN Code API for Forms',
            'category' => 'Developer API',
            'subsection' => 'Autocomplete and UX',
            'region' => 'Digital Form Ecosystems',
            'anchor_city' => 'Kochi',
            'map' => ['lat' => 9.9312, 'lon' => 76.2673, 'zoom' => 7],
            'stats' => ['population_mn' => 150, 'urbanization_pct' => 59.1, 'post_offices' => 9800, 'air_cargo_mt' => 0.84],
        ],
        [
            'slug' => 'pin-code-validation-api-use-cases',
            'title' => 'PIN Code Validation API Use Cases for Ecommerce Checkout',
            'category' => 'Developer API',
            'subsection' => 'Validation Workflows',
            'region' => 'Digital Commerce India',
            'anchor_city' => 'Bengaluru',
            'map' => ['lat' => 12.9716, 'lon' => 77.5946, 'zoom' => 6],
            'stats' => ['population_mn' => 230, 'urbanization_pct' => 69.7, 'post_offices' => 16300, 'air_cargo_mt' => 1.72],
        ],
    ];
}

function getMenuPageBySlug(string $slug): ?array
{
    foreach (getMenuPages() as $page) {
        if ($page['slug'] === $slug) {
            return $page;
        }
    }

    return null;
}

function renderMenuPageContent(array $page): string
{
    $seed = abs(crc32($page['slug']));
    $themes = ['address quality', 'delivery reliability', 'service accessibility', 'supply chain timing', 'regional planning'];
    $riskFocus = ['seasonal disruption', 'documentation mismatch', 'lane imbalance', 'sorting dependency', 'infrastructure stress'];
    $opsFocus = ['node planning', 'route optimization', 'capacity buffering', 'service prioritization', 'data governance'];

    $theme = $themes[$seed % count($themes)];
    $risk = $riskFocus[$seed % count($riskFocus)];
    $ops = $opsFocus[$seed % count($opsFocus)];

    $region = htmlspecialchars($page['region'], ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8');
    $anchor = htmlspecialchars($page['anchor_city'], ENT_QUOTES, 'UTF-8');
    $category = htmlspecialchars($page['category'], ENT_QUOTES, 'UTF-8');
    $subsection = htmlspecialchars($page['subsection'], ENT_QUOTES, 'UTF-8');

    $words = [];
    for ($i = 1; $i <= 16; $i++) {
        $words[] = "<p class=\"mb-4 text-gray-700\">\n            In strategic review cycle {$i}, planners evaluating {$title} should map settlement growth, institutional demand, and recurring dispatch behavior at PIN granularity. For {$region}, operational teams can benchmark {$theme} through office-level throughput, undelivered article causes, and cross-mode transfers from rail, road, and airport nodes near {$anchor}. A robust model links address normalization rules with dispatch windows, escalation paths, and district-level service commitments so that citizens, enterprises, and public agencies experience predictable turnaround even during surge periods. This planning lens supports cost discipline while raising trust in communication, commerce, and governance workflows.\n        </p>";
        $words[] = "<p class=\"mb-4 text-gray-700\">\n            Decision-makers also monitor {$risk} by combining weather exposure, corridor congestion, and local administrative boundaries that influence first-attempt delivery rates. In {$subsection} projects under {$category}, the priority is to maintain clean geospatial referencing, maintain standardized locality names, and create contingency lanes that can absorb routing shocks. Teams typically align service maps with ward expansions, industrial permits, education clusters, and healthcare load so each PIN segment remains measurable, auditable, and ready for expansion. This evidence-driven method improves compliance, customer communication, and long-term resilience at once.\n        </p>";
    }

    $lat = (float)$page['map']['lat'];
    $lon = (float)$page['map']['lon'];
    $zoom = (int)$page['map']['zoom'];

    $population = (float)$page['stats']['population_mn'];
    $urban = (float)$page['stats']['urbanization_pct'];
    $offices = (int)$page['stats']['post_offices'];
    $cargo = (float)$page['stats']['air_cargo_mt'];

    ob_start();
    ?>
    <article class="bg-white rounded-2xl shadow p-6 md:p-8 leading-7">
        <h1 class="text-3xl md:text-4xl font-bold text-indigo-700 mb-4"><?= $title ?></h1>
        <p class="text-sm text-gray-500 mb-6">Category: <?= $category ?> · Section: <?= $subsection ?> · Region: <?= $region ?></p>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-3">Professional Overview</h2>
            <p class="mb-4 text-gray-700">
                This reference page is designed for policy analysts, logistics operators, researchers, and enterprise planning teams who need deep insights for <?= $title ?>. The framework combines geography, addressing quality, transport behavior, and service readiness indicators so practitioners can build realistic plans, improve route design, and reduce avoidable service failures. The analysis emphasizes measurable outcomes, district-sensitive execution, and standardized operational language suitable for institutional and business workflows.
            </p>
            <p class="mb-4 text-gray-700">
                The analytical anchor for this page is <?= $anchor ?>, used as a practical viewpoint for comparing corridor load, administrative reach, and multimodal transfer dependencies. While local conditions vary across districts, the principles here can be adapted by replacing benchmark values with office-level or state-level datasets available to planning teams.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-3">Regional Map Snapshot</h2>
            <iframe
                title="<?= $title ?> map"
                class="w-full h-72 rounded-lg border"
                loading="lazy"
                src="https://www.openstreetmap.org/export/embed.html?bbox=<?= $lon - 2 ?>%2C<?= $lat - 2 ?>%2C<?= $lon + 2 ?>%2C<?= $lat + 2 ?>&layer=mapnik&marker=<?= $lat ?>%2C<?= $lon ?>"
            ></iframe>
            <p class="text-sm text-gray-600 mt-2">Map center: <?= $anchor ?> (Lat <?= number_format($lat, 4) ?>, Lon <?= number_format($lon, 4) ?>), reference zoom <?= $zoom ?>.</p>
        </section>

        <section class="mb-8 overflow-x-auto">
            <h2 class="text-2xl font-semibold mb-3">Key Planning Indicators</h2>
            <table class="min-w-full border border-gray-200 text-sm">
                <tbody>
                    <tr class="border-b"><th class="text-left p-3 bg-gray-50">Population Coverage (mn)</th><td class="p-3"><?= number_format($population, 1) ?></td></tr>
                    <tr class="border-b"><th class="text-left p-3 bg-gray-50">Urbanization (%)</th><td class="p-3"><?= number_format($urban, 1) ?></td></tr>
                    <tr class="border-b"><th class="text-left p-3 bg-gray-50">Linked Post Offices</th><td class="p-3"><?= number_format($offices) ?></td></tr>
                    <tr><th class="text-left p-3 bg-gray-50">Air Cargo Dependency (mn tonnes)</th><td class="p-3"><?= number_format($cargo, 2) ?></td></tr>
                </tbody>
            </table>
        </section>

        <section>
            <h2 class="text-2xl font-semibold mb-3">Detailed Assessment and Execution Notes</h2>
            <?= implode("\n", $words) ?>
        </section>
    </article>
    <?php

    return (string)ob_get_clean();
}
