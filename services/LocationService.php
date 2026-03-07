<?php

declare(strict_types=1);

class LocationService
{
    public function __construct(private PDO $pdo, private CacheService $cache)
    {
    }

    public function search(string $query): array
    {
        return $this->cache->remember('search:' . strtolower($query), function () use ($query) {
            if (preg_match('/^\d{6}$/', $query)) {
                return $this->getPincodePage($query);
            }

            $like = '%' . strtolower($query) . '%';
            $sql = "SELECT officename, pincode, district, statename FROM post_offices WHERE LOWER(officename) LIKE :q OR LOWER(district) LIKE :q OR LOWER(statename) LIKE :q ORDER BY pincode LIMIT 25";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['q' => $like]);
            return $stmt->fetchAll();
        });
    }

    public function getStates(): array
    {
        return $this->cache->remember('states:list', function () {
            $sql = "SELECT DISTINCT statename AS name, LOWER(REPLACE(statename, ' ', '-')) AS slug FROM post_offices ORDER BY statename";
            return $this->pdo->query($sql)->fetchAll();
        });
    }

    public function getStatePage(string $stateSlug): array
    {
        $state = str_replace('-', ' ', $stateSlug);
        return $this->cache->remember('state:' . $stateSlug, function () use ($state) {
            $summary = $this->fetchOne("SELECT statename, COUNT(DISTINCT district) district_count, COUNT(*) office_count FROM post_offices WHERE LOWER(statename) = LOWER(:state)", ['state' => $state]);
            $districts = $this->fetchAll("SELECT DISTINCT district, LOWER(REPLACE(district, ' ', '-')) slug FROM post_offices WHERE LOWER(statename)=LOWER(:state) ORDER BY district", ['state' => $state]);
            return ['summary' => $summary, 'districts' => $districts];
        });
    }

    public function getDistrictPage(string $districtSlug): array
    {
        $district = str_replace('-', ' ', $districtSlug);
        return $this->cache->remember('district:' . $districtSlug, function () use ($district) {
            $summary = $this->fetchOne("SELECT district, MAX(statename) statename, COUNT(DISTINCT pincode) pincode_count, COUNT(*) office_count FROM post_offices WHERE LOWER(district)=LOWER(:district)", ['district' => $district]);
            $areas = $this->fetchAll("SELECT DISTINCT officename AS area, pincode, LOWER(REPLACE(officename, ' ', '-')) slug FROM post_offices WHERE LOWER(district)=LOWER(:district) ORDER BY officename LIMIT 150", ['district' => $district]);
            return ['summary' => $summary, 'areas' => $areas];
        });
    }

    public function getAreaPage(string $areaSlug): array
    {
        $area = str_replace('-', ' ', $areaSlug);
        return $this->cache->remember('area:' . $areaSlug, function () use ($area) {
            $offices = $this->fetchAll("SELECT officename, pincode, district, statename, delivery FROM post_offices WHERE LOWER(officename)=LOWER(:area) ORDER BY pincode", ['area' => $area]);
            return ['summary' => $offices[0] ?? null, 'offices' => $offices];
        });
    }

    public function getPincodePage(string $pincode): array
    {
        return $this->cache->remember('pincode:' . $pincode, function () use ($pincode) {
            $offices = $this->fetchAll("SELECT officename, pincode, district, statename, divisionname, regionname, circlename, delivery FROM post_offices WHERE pincode=:pin ORDER BY officename", ['pin' => $pincode]);
            $summary = $offices[0] ?? null;
            return ['summary' => $summary, 'offices' => $offices];
        });
    }

    public function getNearbyPincodes(string $district, string $currentPincode): array
    {
        return $this->fetchAll("SELECT DISTINCT pincode FROM post_offices WHERE LOWER(district)=LOWER(:district) AND pincode <> :pin ORDER BY pincode LIMIT 10", ['district' => $district, 'pin' => $currentPincode]);
    }

    public function getDistrictLinks(string $state): array
    {
        return $this->fetchAll("SELECT DISTINCT district, LOWER(REPLACE(district, ' ', '-')) slug FROM post_offices WHERE LOWER(statename)=LOWER(:state) ORDER BY district LIMIT 10", ['state' => $state]);
    }

    public function getStateLinks(string $state): array
    {
        return $this->fetchAll("SELECT DISTINCT statename, LOWER(REPLACE(statename, ' ', '-')) slug FROM post_offices WHERE LOWER(statename) <> LOWER(:state) ORDER BY statename LIMIT 10", ['state' => $state]);
    }

    public function getSitemapRows(string $dimension, int $limit = 50000, int $offset = 0): array
    {
        return match ($dimension) {
            'pincode' => $this->fetchAll("SELECT DISTINCT pincode AS slug FROM post_offices ORDER BY pincode LIMIT {$limit} OFFSET {$offset}"),
            'district' => $this->fetchAll("SELECT DISTINCT LOWER(REPLACE(district,' ','-')) AS slug FROM post_offices ORDER BY district LIMIT {$limit} OFFSET {$offset}"),
            'state' => $this->fetchAll("SELECT DISTINCT LOWER(REPLACE(statename,' ','-')) AS slug FROM post_offices ORDER BY statename LIMIT {$limit} OFFSET {$offset}"),
            default => [],
        };
    }

    public function listTables(): array
    {
        return $this->fetchAll('SHOW TABLES');
    }

    private function fetchAll(string $sql, array $bindings = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    private function fetchOne(string $sql, array $bindings = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
