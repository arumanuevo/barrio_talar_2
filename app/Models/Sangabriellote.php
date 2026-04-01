<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Grimzy\LaravelMysqlSpatial\Types\Polygon;
class Sangabriellote extends Model
{
    protected $table = 'sangabriellotes';
    use SpatialTrait;
    //protected $fillable = ['id', 'OGR_FID', 'SHAPE', 'lote'];
    protected $fillable = ['id', 'lote'];

    protected $spatialFields = [
        'SHAPE'
    ];

    public function getGeomAttribute()
    {
        $shape = $this->SHAPE;

        if ($shape instanceof Polygon) {
            $coordinates = $shape->getCoordinates();
            $geometry = [
                'type' => 'Polygon',
                'coordinates' => $coordinates,
            ];

            return json_encode($geometry);
        }

        return null;
    }
}
