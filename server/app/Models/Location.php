<?php
/**
 * This model contains cities, regions, countries.
 *
 * city:
 * 划分地理位置的最低行政单位，可以是城市，城市当中的一大片区域，城镇等，拥有经纬度数据，
 * 主要用于定位cms, bbs, blog，user等所属的区域，以及按照区域查找内容等等.
 *
 * region:
 * 按照行政或者地理区域划分的省，区域，郡等等，包括多个城市/城镇等，所包含的城市可能有交叉
 *
 * country:
 * 最顶级的行政划分，主要用于英美
 * UK countries include: England, Scotland, Welsh and North Ireland + Ireland.
 * US countries include: USA and Canada
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';
    public $timestamps = false;
}
