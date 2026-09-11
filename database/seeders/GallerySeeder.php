<?php

namespace Database\Seeders;

use App\Models\Media;
use Illuminate\Database\Seeder;

/**
 * Photographs for the public gallery.
 *
 * Membership of the gallery is recorded on the media record itself rather than
 * inferred from approval, so that an image approved for a leadership profile or
 * an award certificate does not appear here as well. Editors add and remove
 * photographs from the set in the administration panel.
 *
 * Each entry carries its real dimensions, so the responsive `srcset` never
 * offers a browser a variant larger than the original.
 */
class GallerySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->photographs() as [$path, $title, $width, $height]) {
            $media = Media::firstOrCreate(
                ['path' => $path],
                [
                    'title' => $title,
                    'disk' => 'cloudinary',
                    'mime' => 'image/jpeg',
                    'size' => 0,
                    'alt' => $title.'.',
                    'variants' => ['width' => $width, 'height' => $height],
                    'collection' => Media::GALLERY,
                    'approved' => true,
                ],
            );

            // A photograph may already exist because it also illustrates a page,
            // in which case ContentSeeder created it first and without a
            // collection. Place it in the gallery without touching the title or
            // alt text, which an editor may since have improved.
            if ($media->collection !== Media::GALLERY) {
                $media->forceFill(['collection' => Media::GALLERY, 'approved' => true])->save();
            }
        }
    }

    /**
     * @return list<array{0: string, 1: string, 2: int, 3: int}>
     */
    private function photographs(): array
    {
        return [
            ['image/upload/v1789056074/linkage.jpg', 'Building linkages between co-operatives', 1166, 640],
            ['image/upload/v1789056074/group_volunteer_shot_small.jpg', 'Volunteers gathered at a foundation event', 256, 145],
            ['image/upload/v1789056073/ica.jpg', 'International co-operative engagement', 688, 759],
            ['image/upload/v1789056073/gm.jpg', 'A general meeting of members', 1632, 1224],
            ['image/upload/v1789056071/gl44.jpg', 'Foundation activity photograph', 1618, 1130],
            ['image/upload/v1789056070/gl43.jpg', 'Foundation activity photograph', 1580, 918],
            ['image/upload/v1789056067/gl39.jpg', 'Foundation activity photograph', 640, 480],
            ['image/upload/v1789056066/gl37.jpg', 'Foundation activity photograph', 1290, 736],
            ['image/upload/v1789056067/gl38.jpg', 'Foundation activity photograph', 1449, 949],
            ['image/upload/v1789056064/gl34.jpg', 'Foundation activity photograph', 1600, 700],
            ['image/upload/v1789056065/gl36.jpg', 'Foundation activity photograph', 800, 510],
            ['image/upload/v1789056064/gl35.jpg', 'Foundation activity photograph', 600, 400],
            ['image/upload/v1789056062/gl33.jpg', 'Foundation activity photograph', 555, 417],
            ['image/upload/v1789056063/gl29.jpg', 'Foundation activity photograph', 2000, 1035],
            ['image/upload/v1789056062/gl31.jpg', 'Foundation activity photograph', 1420, 762],
            ['image/upload/v1789056061/gl25.jpg', 'Foundation activity photograph', 640, 480],
            ['image/upload/v1789056061/gl24.jpg', 'Foundation activity photograph', 1802, 1022],
            ['image/upload/v1789056060/gl17.jpg', 'Foundation activity photograph', 1880, 1012],
            ['image/upload/v1789056057/gl5.jpg', 'Foundation activity photograph', 1694, 1292],
            ['image/upload/v1789056058/gl7.jpg', 'Foundation activity photograph', 1824, 1078],
            ['image/upload/v1789056059/gl8.jpg', 'Foundation activity photograph', 1940, 1294],
            ['image/upload/v1789056058/gl4.jpg', 'Foundation activity photograph', 1697, 972],
            ['image/upload/v1789056057/gl6.jpg', 'Foundation activity photograph', 797, 570],
            ['image/upload/v1789056056/gl3.jpg', 'Foundation activity photograph', 1795, 1346],
            ['image/upload/v1789056054/gl2.jpg', 'Foundation activity photograph', 918, 674],
            ['image/upload/v1789056053/gl1.jpg', 'Foundation activity photograph', 640, 480],
            ['image/upload/v1789056052/g41.jpg', 'Foundation activity photograph', 1610, 1036],
            ['image/upload/v1789056041/g28.jpg', 'Foundation activity photograph', 254, 145],
            ['image/upload/v1789056042/g29.jpg', 'Foundation activity photograph', 245, 145],
            ['image/upload/v1789056039/g24.jpg', 'Foundation activity photograph', 254, 144],
            ['image/upload/v1789056037/g21.jpg', 'Foundation activity photograph', 256, 145],
            ['image/upload/v1789056038/g23.jpg', 'Foundation activity photograph', 256, 145],
            ['image/upload/v1789056036/g19.jpg', 'Foundation activity photograph', 258, 145],
            ['image/upload/v1789056033/g15.jpg', 'Foundation activity photograph', 258, 145],
            ['image/upload/v1789056031/g13.jpg', 'Foundation activity photograph', 258, 145],
            ['image/upload/v1789056030/g11.jpg', 'Foundation activity photograph', 258, 145],
            ['image/upload/v1789056030/g12.jpg', 'Foundation activity photograph', 258, 145],
            ['image/upload/v1789056029/g9.jpg', 'Foundation activity photograph', 258, 145],
            ['image/upload/v1789056029/g10.jpg', 'Foundation activity photograph', 258, 145],
            ['image/upload/v1789056026/g5.jpg', 'Foundation activity photograph', 256, 145],
        ];
    }
}
