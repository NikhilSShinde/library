<?php
namespace App\PiplModules\roles\Models;
use App\PiplModules\roles\Traits\Slugable;
use Illuminate\Database\Eloquent\Model;
use App\PiplModules\roles\Traits\RoleHasRelations;
use App\PiplModules\roles\Contracts\RoleHasRelations as RoleHasRelationsContract;

class Role extends Model implements RoleHasRelationsContract
{
    use Slugable, RoleHasRelations;
    public $timestamps  = true;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'slug', 'description', 'level','created_at','updated_at'];

    /**
     * Create a new model instance.
     *
     * @param array $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if ($connection = config('roles.connection')) {
            $this->connection = $connection;
        }
    }
	
	
}
