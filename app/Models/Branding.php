<?php

namespace App\Models;

use PDO;

class Branding
{
    public static function current(PDO $db): array
    {
        $stmt = $db->query('SELECT * FROM branding LIMIT 1');
        $branding = $stmt->fetch();
        return $branding ?: [
            'name_ru' => 'ServiceDesk',
            'name_en' => 'ServiceDesk',
            'slogan_ru' => 'Поддержка, которая рядом',
            'slogan_en' => 'Support that stays close',
            'logo_url' => '',
            'color_primary' => '#2563eb',
            'color_secondary' => '#14b8a6',
        ];
    }

    public static function update(PDO $db, array $data): void
    {
        $stmt = $db->query('SELECT id FROM branding LIMIT 1');
        $current = $stmt->fetch();
        if ($current) {
            $fields = [];
            foreach ($data as $key => $value) {
                $fields[] = "$key = :$key";
            }
            $sql = 'UPDATE branding SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $data['id'] = $current['id'];
            $stmt = $db->prepare($sql);
            $stmt->execute($data);
            return;
        }
        $stmt = $db->prepare('INSERT INTO branding (name_ru, name_en, slogan_ru, slogan_en, logo_url, color_primary, color_secondary) VALUES (:name_ru, :name_en, :slogan_ru, :slogan_en, :logo_url, :color_primary, :color_secondary)');
        $stmt->execute($data);
    }
}
