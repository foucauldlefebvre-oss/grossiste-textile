<?php

namespace App\Helpers;

use App\Models\Quote;
use Illuminate\Support\Facades\DB;

class BatHelper
{
    /**
     * Detect SVG template slug from product name + description.
     */
    public static function detectTemplate(string $name, string $description = ''): string
    {
        $text = strtolower($name . ' ' . $description);

        $patterns = [
            // Chemises / Oxford
            '/chemise|oxford|poplin|popeline|blouse/'    => 'chemise',

            // Sweat zip + capuche
            '/sweat.*zip.*capuch|zip.*capuch|zipp[ée].*capuch|full.?zip.*hood|hoodie.*zip/u'
                                                          => 'sweat-zip-capuche',

            // Sweat demi-zip / quart-zip / col montant zippé
            '/demi.?zip|half.?zip|1\/4.?zip|quarter.?zip|col.?montant.*zip|zip.*col.?montant/u'
                                                          => 'sweat-quart-zip',

            // Sweat zip complet sans capuche
            '/sweat.*zip|zip.*sweat|zipp[ée]|full.?zip/u' => 'sweat-zip',

            // Sweat capuche sans zip
            '/hoodie|sweat.*capuch|capuch|hooded/u'       => 'sweat-capuche',

            // Sweat col rond / sweatshirt générique
            '/sweatshirt|sweater|sweat|pullover/u'        => 'sweat-col-rond',

            // Polo femme (détection spécifique avant polo générique)
            '/polo.*(woman|lady|girl|femm)|(woman|lady|girl|femm).*polo/u' => 'polo-femme',

            // Polo générique
            '/polo/'                                      => 'polo-homme',

            // T-shirt
            '/t[\-\s]?shirt|tshirt|tee\b/'                => 'tshirt-homme',

            // Vestes
            '/veste|jacket|softshell|parka|manteau|coupe.?vent/u' => 'veste',

            // Accessoires
            '/casquette|cap\b|hat\b|bonnet/'              => 'casquette',
            '/tote|sac\b|bag\b/'                          => 'tote-bag',
        ];

        foreach ($patterns as $pattern => $template) {
            if (preg_match($pattern, $text)) {
                return $template;
            }
        }

        return 'tshirt-homme';
    }

    /**
     * Max dimensions in cm for a given format.
     */
    public static function formatDimensions(string $format): array
    {
        return match ($format) {
            'A7' => ['width' => 10.5, 'height' => 7.4],
            'A6' => ['width' => 14.8, 'height' => 10.5],
            'A5' => ['width' => 21.0, 'height' => 14.8],
            'A4' => ['width' => 29.7, 'height' => 21.0],
            'A3' => ['width' => 42.0, 'height' => 29.7],
            default => ['width' => 10.5, 'height' => 7.4],
        };
    }

    /**
     * Map zone slug to French label.
     */
    public static function zoneLabel(string $zone): string
    {
        return match ($zone) {
            'poitrine_gauche' => 'Poitrine gauche',
            'poitrine_droite' => 'Poitrine droite',
            'poitrine_centre' => 'Poitrine centré',
            'dos_centre'      => 'Dos centré',
            'dos_haut'        => 'Dos haut',
            'manche_gauche'   => 'Manche gauche',
            'manche_droite'   => 'Manche droite',
            'col'             => 'Col',
            ''                => 'Non défini',
            default           => $zone,
        };
    }

    /**
     * Zone slug to SVG anchor position in pixels.
     */
    public static function zonePosition(string $positionLabel): array
    {
        return match ($positionLabel) {
            'Poitrine gauche'  => ['x' => 252, 'y' => 155],
            'Poitrine droite'  => ['x' => 88,  'y' => 155],
            'Poitrine centré'  => ['x' => 170, 'y' => 155],
            'Dos centré'       => ['x' => 170, 'y' => 190],
            'Dos haut'         => ['x' => 170, 'y' => 155],
            'Manche gauche'    => ['x' => 55,  'y' => 148],
            'Manche droite'    => ['x' => 330, 'y' => 148],
            'Col'              => ['x' => 185, 'y' => 120],
            default            => ['x' => 252, 'y' => 155],
        };
    }

    /**
     * Is the hex color dark? (luminance < 160)
     */
    public static function isDark(string $hex): bool
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) {
            return false;
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return ($r * 299 + $g * 587 + $b * 114) / 1000 < 160;
    }

    /**
     * Detect template using category tree, then fallback to name+description+cut.
     */
    public static function detectTemplateByCategory(
        int $productId,
        string $productName,
        string $cut,
        string $description = ''
    ): string {
        $categoryId = DB::table('product_categories')
            ->where('product_id', $productId)
            ->value('category_id');

        if ($categoryId) {
            $rootSlug = self::getRootCategorySlug($categoryId);
            $template = self::templateFromCategorySlug($rootSlug, $productName, $cut, $description);
            if ($template) {
                return $template;
            }
        }

        // Fallback by product name + description
        $base = self::detectTemplate($productName, $description);

        // Apply cut to generic templates
        if ($base === 'tshirt-homme' && $cut === 'femme') {
            return 'tshirt-femme';
        }
        if ($base === 'polo-homme' && $cut === 'femme') {
            return 'polo-femme';
        }

        return $base;
    }

    private static function getRootCategorySlug(int $catId): string
    {
        $cat = DB::table('categories')->find($catId);
        if (! $cat) {
            return '';
        }
        if (! $cat->parent_id) {
            return $cat->slug;
        }

        return self::getRootCategorySlug($cat->parent_id);
    }

    private static function templateFromCategorySlug(
        string $slug,
        string $name,
        string $cut,
        string $description = ''
    ): ?string {
        $text = strtolower($name . ' ' . $description);
        $isFemme = $cut === 'femme';

        return match (true) {
            $slug === 't-shirts' => $isFemme ? 'tshirt-femme' : 'tshirt-homme',
            $slug === 'polos' => $isFemme ? 'polo-femme' : 'polo-homme',
            $slug === 'sweats' => match (true) {
                (bool) preg_match('/hoodie|capuche|hooded/i', $text) => 'sweat-capuche',
                (bool) preg_match('/zip.*(hood|cap)|(hood|cap).*zip/i', $text) => 'sweat-zip-capuche',
                (bool) preg_match('/demi.?zip|half.?zip|1\/4|quarter/i', $text) => 'sweat-quart-zip',
                (bool) preg_match('/zip|zipp/i', $text) => 'sweat-zip',
                default => 'sweat-col-rond',
            },
            in_array($slug, ['coupe-vent-softshell', 'manteaux-parkas', 'blazers', 'polaires']) => 'veste',
            $slug === 'pulls-gilets' => 'sweat-col-rond',
            $slug === 'chemises' => 'chemise',
            $slug === 'sport' => $isFemme ? 'tshirt-femme' : 'tshirt-homme',
            default => null,
        };
    }

    /**
     * Build JSON {"groupId": "template", ...} for all marking groups.
     */
    public static function buildTemplatesJson(Quote $quote): string
    {
        $items = $quote->items()
            ->with(['product' => fn ($q) => $q->select('id', 'name', 'cut', 'description', 'short_description')])
            ->get();
        $groups = $items->groupBy('marking_group');
        $templates = [];

        foreach ($groups as $groupId => $groupItems) {
            $firstItem = $groupItems->first();
            if (! $firstItem?->product) {
                continue;
            }

            $desc = $firstItem->product->description
                ?? $firstItem->product->short_description
                ?? '';

            $templates[(string) $groupId] = self::detectTemplateByCategory(
                $firstItem->product->id,
                $firstItem->product->name,
                $firstItem->product->cut ?? 'homme',
                $desc
            );
        }

        return json_encode((object) $templates);
    }

    /**
     * Get template for a specific marking group from the JSON field.
     */
    public static function getTemplate(Quote $quote, int $groupId): string
    {
        $raw = $quote->bat_svg_template;
        if (! $raw) {
            return 'tshirt-homme';
        }

        $templates = json_decode($raw, true);

        // Old format: plain string
        if (! is_array($templates)) {
            return $raw;
        }

        return $templates[(string) $groupId]
            ?? $templates['0']
            ?? 'tshirt-homme';
    }
}
