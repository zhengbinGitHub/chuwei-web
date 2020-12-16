<?php
/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2020-11-05
 * Time: 22:26
 */

namespace ChuWei\Client\Web\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $table = 'tenants';

    protected $guarded = [];
}