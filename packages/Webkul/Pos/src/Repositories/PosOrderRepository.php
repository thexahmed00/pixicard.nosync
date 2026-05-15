<?php

namespace Webkul\Pos\Repositories;

use Intervention\Image\Facades\Image;
use Webkul\Core\Eloquent\Repository;
use Webkul\Pos\Contracts\PosOrder;

class PosOrderRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return PosOrder::class;
    }

    /**
     * Generate order barcode
     */
    public function generateOrderBarcode(int $id): string
    {
        $barcodeDirectory = storage_path('app/public/order_barcode/');

        if (! file_exists($barcodeDirectory)) {
            mkdir(storage_path('app/public/order_barcode/'), 0777, true);
        }

        require_once __DIR__.'/../barcode.php';

        $filepath = "$barcodeDirectory$id.png";

        $width = core()->getConfigData('pos.settings.barcode.width') ?: 100;

        $height = core()->getConfigData('pos.settings.barcode.height') ?: 50;

        $imageType = core()->getConfigData('pos.configuration.barcode.image_type');

        barcode($filepath, $id, $width, $height, $imageType);

        Image::cache(fn ($image) => $image->make($filepath)->resize(300, 200)->greyscale());

        return "/order_barcode/$id.png";
    }
}
